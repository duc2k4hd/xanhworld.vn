<li>
    <div class="tree-item" data-category-id="{{ $item['id'] }}">
        <span class="tree-toggle">
            @if(!empty($item['children']))
                ▶
            @else
                &nbsp;
            @endif
        </span>
        <span style="flex:1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $item['name'] }}</span>
        @if(($item['children_count'] ?? 0) > 0)
            <span class="children-badge">
                {{ $item['children_count'] }}
            </span>
        @endif
    </div>
    @if(!empty($item['children']))
        <ul class="tree-children">
            @foreach($item['children'] as $child)
                @include('admins.categories.partials.tree-item', ['item' => $child, 'level' => $level + 1])
            @endforeach
        </ul>
    @endif
</li>

