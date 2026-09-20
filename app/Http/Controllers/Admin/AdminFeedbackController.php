<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class AdminFeedbackController extends Controller
{
    public function index()
    {
        $this->ensureFeedbackTableExists();
        $feedbacks = Feedback::with(['pengguna', 'mitra'])
            ->orderBy('dibuat_pada', 'desc')
            ->paginate(15);

        return view('admin.feedback.index', compact('feedbacks'));
    }

    public function reply(Request $request, string $id)
    {
        $this->ensureFeedbackTableExists();
        $request->validate([
            'jawaban' => 'required|string',
        ]);

        $feedback = Feedback::findOrFail($id);
        $feedback->update([
            'jawaban'        => $request->jawaban,
            'status'         => 'DIJAWAB',
            'dijawab_pada'   => now(),
        ]);

        // Kirim notifikasi ke pengirim (nasabah atau mitra)
        \App\Http\Controllers\Api\NotificationController::kirim(
            $feedback->id_pengguna,
            'Balasan Kritik & Saran 💬',
            'Admin Desa membalas pesan Anda: "' . \Illuminate\Support\Str::limit($request->jawaban, 80) . '"',
            'FEEDBACK'
        );

        \App\Services\AuditLogger::log(
            'BALAS_FEEDBACK',
            "Membalas aspirasi/kritik warga: \"" . \Illuminate\Support\Str::limit($feedback->pesan, 50) . "\" dengan balasan: \"" . \Illuminate\Support\Str::limit($request->jawaban, 60) . "\"",
            'FEEDBACK',
            'UPDATE',
            'SUKSES',
            ['feedback_id' => $id, 'pesan_warga' => $feedback->pesan, 'jawaban_admin' => $request->jawaban]
        );

        return redirect()->route('admin.feedback.index')->with('success', 'Balasan berhasil dikirim.');
    }

    public function destroy(string $id)
    {
        $fb = Feedback::findOrFail($id);
        $cuplikan = \Illuminate\Support\Str::limit($fb->pesan, 60);
        $fb->delete();

        \App\Services\AuditLogger::log(
            'HAPUS_FEEDBACK',
            "Menghapus pesan kritik & saran warga: \"{$cuplikan}\"",
            'FEEDBACK',
            'DELETE',
            'PERINGATAN',
            ['feedback_id' => $id]
        );

        return redirect()->route('admin.feedback.index')->with('success', 'Pesan kritik & saran berhasil dihapus.');
    }

    private function ensureFeedbackTableExists()
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('kritik_saran')) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('kritik_saran', 'status')) {
                    \Illuminate\Support\Facades\Schema::table('kritik_saran', function ($table) {
                        $table->string('status', 30)->default('MENUNGGU')->after('jawaban');
                    });
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('kritik_saran', 'pengirim')) {
                    \Illuminate\Support\Facades\Schema::table('kritik_saran', function ($table) {
                        $table->string('pengirim', 30)->default('NASABAH')->after('kategori');
                    });
                }
                // Auto-sync status untuk data yang sudah memiliki jawaban
                \Illuminate\Support\Facades\DB::table('kritik_saran')
                    ->whereNotNull('jawaban')
                    ->where('jawaban', '!=', '')
                    ->where(function ($q) {
                        $q->whereNull('status')->orWhere('status', '!=', 'DIJAWAB');
                    })
                    ->update(['status' => 'DIJAWAB']);
            }
        } catch (\Exception $e) {
            // Abaikan bila ada exception atau sudah ada
        }
    }
}
