<?php
/*
Template Name: Custom Post
 */

get_header();

?>
<!-- Add this HTML to your template file where you want to display the posts -->

<select id="category-select">
    <?php
    $categories = get_categories();
    foreach ($categories as $category) {
        echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
    }
    ?>
</select>
<button id="fetch-posts-button">Fetch Posts</button>
<div id="posts-container"></div>


<?php
get_footer();
