<?php

get_header();
?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main">
                <?php
                $id = get_the_id();
                $phone = get_post_meta($id, "agencyPhone", true);
                $email = get_post_meta($id, "agencyEmail", true);
                $address = get_post_meta($id, "agencyAddress", true);


                while(have_posts()) {
                    the_post(); //Stop the iterator on the post
                    
                    get_the_post_thumbnail(); //Display the thumbnail
                                       
                    the_title(); //The title
                    
                    echo $phone . " " . $email . " " . $address;
                } 
                
                ?>
        </main>
    </div>

<?php
get_footer();