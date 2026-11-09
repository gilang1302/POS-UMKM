@if ($paginator->hasPages())
  <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-2">
    <small class="text-muted">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }} ({{ $paginator->total() }} data)</small>
    <div class="btn-group">
      <a class="btn btn-sm btn-outline-secondary {{ $paginator->onFirstPage() ? 'disabled' : '' }}" href="{{ $paginator->previousPageUrl() ?? '#' }}">Sebelumnya</a>
      <a class="btn btn-sm btn-outline-secondary {{ $paginator->hasMorePages() ? '' : 'disabled' }}" href="{{ $paginator->nextPageUrl() ?? '#' }}">Berikutnya</a>
    </div>
  </div>
@endif
