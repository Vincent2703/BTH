<?php
    if(have_posts()) {
        get_header();                    
        while(have_posts()) { //?
            the_post();

            $idPost = get_the_id();
            
            $agency = get_the_terms($idPost, "agentAgency");
            var_dump($agency);
            
?>
        <div id="primary" class="content-area">
            <main id="main" class="site-main">
                <span class="titleAd"><h1><?= the_title(); ?></h1></span>
                <span class="subtitleAd">Agence <?= $agency ;?></span>
            </main>
        </div>
<?php
        }
    }
    get_footer();
?>