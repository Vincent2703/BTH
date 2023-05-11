<?php
    if(!defined("ABSPATH")) {
        exit; //Exit if accessed directly
    }
    if(have_posts()) {
        get_header();                    
        while(have_posts()) { //?
            the_post();

            $idPost = get_the_id();
            $parent = get_post_parent();
            $agency = get_the_title($parent);
            
?>
        <div id="primary" class="content-area">
            <main id="main" class="site-main">
                <span class="titleAgent"><h1><?= the_title(); ?></h1></span>
                <span class="subtitleAgent">Agence <a href="<?= get_post_permalink($parent);?>"><?= $agency ;?></a></span>
                <div class="detailsAgent">
                    <?= get_the_post_thumbnail($idPost, "thumbnail"); ?>
                    <ul>
                        <?php if($phone = get_post_meta($idPost, "agentPhone", true)) { ?>
                        <li>
                            <span>Téléphone fixe</span>
                            <span><a href="tel:<?= $phone; ?>"><?= $phone; ?></a></span>
                        </li>
                        <?php }if($mobilePhone = get_post_meta($idPost, "agentMobilePhone", true)) { ?>
                        <li>
                            <span>Téléphone portable</span>
                            <span><a href="tel:<?= $mobilePhone; ?>"><?= $mobilePhone; ?></a></span>
                        </li>
                        <?php }if($email = get_post_meta($idPost, "agentEmail", true)) { ?>
                        <li>
                            <span>Adresse mail</span>
                            <span><a href="mailto:<?= $email; ?>"><?= $email; ?></a></span>
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