{{--
    Photo viewer. One per page; any <a data-lbx="group"> opens the whole group,
    so the arrows walk a property's photos without leaving the page.

    Every trigger is a real link to the full image, so with JavaScript off the
    photo still opens — the viewer is an enhancement, not a requirement.
--}}
<div class="lbx" id="lbx" hidden role="dialog" aria-modal="true" aria-label="Photo viewer">
    <button class="lbx-close" type="button" aria-label="Close viewer">&times;</button>
    <button class="lbx-nav lbx-prev" type="button" aria-label="Previous photo">&lsaquo;</button>

    <img src="" alt="">

    <button class="lbx-nav lbx-next" type="button" aria-label="Next photo">&rsaquo;</button>
    <p class="lbx-count" aria-live="polite"></p>
</div>
