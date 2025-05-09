<!-- Adding esc_url() for security -->
<form class="search-form" method="get" action="<?php echo esc_url(site_url('/')) ?>">
    <label class="headline headline--medium" for="s">Perform a New Search:</label>
    <div class="search-form-row">
        <input class="s" id="s" type="search" name="s" placeholder="Enter search here...">
        <input class="search-submit btn" type="submit" value="Search">
    </div>
</form>