{{--
    Media viewer. One per page; any <a data-lbx="group"> opens the whole group,
    so the arrows walk a property's photos without leaving the page.

    A trigger carrying data-lbx-type="video" opens in the <video> element
    instead of the <img>; everything else is treated as a photo.

    Every trigger is a real link to the full file, so with JavaScript off the
    photo or video still opens — the viewer is an enhancement, not a
    requirement.
--}}
<div class="lbx" id="lbx" hidden role="dialog" aria-modal="true" aria-label="Media viewer">
    <button class="lbx-close" type="button" aria-label="Close viewer">&times;</button>
    <button class="lbx-nav lbx-prev" type="button" aria-label="Previous">&lsaquo;</button>

    <img src="" alt="">

    {{-- controls, not autoplay: a video that starts talking over the page
         unannounced is worse than one click. --}}
    <video class="lbx-video" hidden controls playsinline preload="metadata"></video>

    <button class="lbx-nav lbx-next" type="button" aria-label="Next">&rsaquo;</button>
    <p class="lbx-count" aria-live="polite"></p>
</div>
