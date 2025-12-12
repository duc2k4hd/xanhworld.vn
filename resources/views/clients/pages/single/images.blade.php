<div class="xanhworld_single_info_images_main_overlay">
    <div class="xanhworld_single_info_images_main_overlay_actions"></div>
    <div class="xanhworld_single_info_images_main_overlay_images">
        @if(!empty($listImg) && is_array($listImg))
            @foreach($listImg as $img)
                <div class="xanhworld_single_info_images_main_overlay_image">
                    <img src="{{ $img }}" alt="Image Overlay" title="Image Overlay">
                </div>
            @endforeach
        @endif
    </div>
</div>