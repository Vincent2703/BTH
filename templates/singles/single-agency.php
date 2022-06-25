<?php
    if(have_posts()) {
        get_header();                    
        while(have_posts()) { //?
            the_post();

            $idPost = get_the_id();
            
?>
        <div id="primary" class="content-area">
            <main id="main" class="site-main">
                <span class="titleAgency"><h1><?= the_title(); ?></h1></span>
                <div class="detailsAgency">
                    <?= get_the_post_thumbnail($idPost, "thumbnail"); ?>
                    <ul>
                        <?php if($phone = get_post_meta($idPost, "agencyPhone", true)) { ?>
                        <li>
                            <span>Téléphone</span>
                            <span><a href="tel:<?= $phone; ?>"><?= $phone; ?></a></span>
                        </li>
                        <?php }if($email = get_post_meta($idPost, "agencyEmail", true)) { ?>
                        <li>
                            <span>Adresse mail</span>
                            <span><a href="mailto:<?= $email; ?>"><?= $email; ?></a></span>
                        </li>
                        <?php }if($address = get_post_meta($idPost, "agencyAddress", true)) { ?>
                        <li>
                            <span>Adresse postale</span>
                            <span><i><?= $address; ?></i></span>
                        </li>
                        <?php } ?>
                    </ul>
                </div>              
            </main>
        </div>
<?php
        }
    }
    get_footer();
?>