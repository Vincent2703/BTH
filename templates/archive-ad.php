<?php get_header(); ?>
        <div id="primary" class="content-area">
            <main id="main" class="site-main">
            <?php if(have_posts()) { //Rajouter une limite de posts?>
                <header class="archive-header has-text-align-center">
                    <h1 class="archive-title">Liste des <span class="color-accent">annonces</span></h1>
                </header>
                <?php
                while(have_posts()) {
                    the_post();
                    $idPost = get_the_id();
                    $typeAd = get_the_terms($idPost, "adTypeAd")[0]->name;
                    $typeProperty = get_the_terms($idPost, "adTypeProperty")[0]->name;
                    $city = get_post_meta($idPost, "adCity", true);
                    $price = get_post_meta($idPost, "adPrice", true);
                    $surface = get_post_meta($idPost, "adSurface", true);
                    $nbRooms = get_post_meta($idPost, "adNbRooms", true);
                    ?>
                    <div class="headerAd">
                        <h3 class="entry-title has-text-align-center"><a href="<?= get_post_permalink($idPost); ?>"><?php the_title(); ?></a></h3>
                        <span class="dateAd">Annonce postée le <?= get_the_date(); ?></span>
                    </div>
                    <figure class="featured-media">
                        <a href="<?= get_post_permalink($idPost); ?>"><?= get_the_post_thumbnail($idPost, array(800, 800)); ?></a>
                    </figure>
                    <table class="inShort">
                        <thead>
                            <tr>
                                <th colspan="2"><?= $city; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><b><?= $typeAd . "</b> : " . $price ; ?>€<?= $typeAd==="Location"?"/mois":'';?></td>
                                <td><b>Type de bien</b> : <?= $typeProperty; ?></td>
                            </tr>
                            <tr>
                                <td><b>Surface habitable</b> : <?= $surface; ?>m²</td>
                                <td><b>Nombre de pièces</b> : <?= $nbRooms; ?></td>
                            </tr>
                        </tbody>
                    </table>
                <?php }
            }else{ ?>
                <?php get_template_part("content", "none" ); ?>
            <?php } ?>

            </main>
	</div>

<?php get_footer(); ?>