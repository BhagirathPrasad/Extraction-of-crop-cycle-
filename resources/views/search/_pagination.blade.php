@if(method_exists($results, 'links'))
    <div class="pagination-wrapper">
        {!! $results->links() !!}
    </div>
@endif
