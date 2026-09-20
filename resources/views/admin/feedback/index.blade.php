@extends('admin.layout')

@section('title', 'Kritik & Saran')
@section('header', 'Kritik, Saran & Aduan')

@section('content')
<div class="space-y-6">
    <!-- Summary Header -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Kritik & Saran dari Nasabah dan Mitra</h3>
            <p class="text-xs text-gray-500 mt-1">Daftar masukan yang dikirimkan nasabah dan mitra pos melalui aplikasi mobile SIRKULO.</p>
        </div>
    </div>

    <!-- Feedbacks List -->
    <div class="space-y-4">
        @forelse($feedbacks as $fb)
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-sm">
                        {{ strtoupper(substr($fb->pengguna?->nama ?? 'W', 0, 2)) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm">{{ $fb->pengguna?->nama ?? 'Anonim' }}</h4>
                        <p class="text-[11px] text-gray-500">No. HP: {{ $fb->pengguna?->telepon ?? '-' }} • Kategori: {{ $fb->kategori }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    {{-- Badge pengirim --}}
                    @if(($fb->pengirim ?? 'NASABAH') === 'MITRA')
                        <span class="px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-full text-xs font-bold">Mitra Pos</span>
                    @else
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-bold">Nasabah</span>
                    @endif
                    @php $sudahDibalas = $fb->status === 'DIJAWAB' || !empty($fb->jawaban); @endphp
                    @if($sudahDibalas)
                        <span class="px-2.5 py-1 bg-gray-100 text-gray-600 border border-gray-200 rounded-full text-xs font-bold">Sudah Dibalas</span>
                    @else
                        <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-bold">Menunggu Respon</span>
                    @endif
                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($fb->dibuat_pada)->diffForHumans() }}</span>
                </div>
            </div>

            <!-- Pesan -->
            <div class="bg-gray-50 p-4 rounded-xl text-xs text-gray-700 leading-relaxed">
                <p class="font-semibold text-gray-800 mb-1">Pesan {{ ($fb->pengirim ?? 'NASABAH') === 'MITRA' ? 'Mitra' : 'Nasabah' }}:</p>
                "{{ $fb->pesan }}"
            </div>

            <!-- Balasan Admin -->
            @if($fb->jawaban)
            <div class="bg-emerald-50/60 border border-emerald-100 p-4 rounded-xl text-xs text-emerald-900 leading-relaxed ml-4">
                <p class="font-semibold text-emerald-800 mb-1 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                    Jawaban Resmi Admin Desa:
                </p>
                "{{ $fb->jawaban }}"
                <p class="text-[10px] text-emerald-600 mt-1">Dibalas pada: {{ \Carbon\Carbon::parse($fb->dijawab_pada)->format('d M Y H:i') }}</p>
            </div>
            @endif

            <!-- Action Form Balas -->
            <div class="pt-2 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <button onclick="document.getElementById('balas-{{ $fb->id }}').classList.toggle('hidden')" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                    {{ $fb->jawaban ? 'Ubah Balasan' : 'Balas Pesan Warga Ini' }}
                </button>

                <form action="{{ route('admin.feedback.destroy', $fb->id) }}" method="POST" onsubmit="return confirm('Hapus pesan masukan ini?')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus Aduan</button>
                </form>
            </div>

            <!-- Formulir Balas Dropdown -->
            <div id="balas-{{ $fb->id }}" class="hidden pt-3 border-t border-gray-100">
                <form action="{{ route('admin.feedback.reply', $fb->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <textarea name="jawaban" rows="3" required placeholder="Tuliskan respon resmi desa untuk warga ini..." class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">{{ $fb->jawaban }}</textarea>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('balas-{{ $fb->id }}').classList.add('hidden')" class="px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                        <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg">Kirim Balasan</button>
                    </div>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white p-12 text-center rounded-2xl border border-gray-100 text-gray-500">
            Belum ada kritik atau saran yang masuk dari warga.
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $feedbacks->links() }}
    </div>
</div>
@endsection
