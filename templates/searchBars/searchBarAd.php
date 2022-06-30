<?php
    require(implode("/", (explode("/", __DIR__, -5)))."/wp-load.php");
    $adTypesAd = get_terms(array(
        "taxonomy" => "adTypeAd",
        "hide_empty" => true,
    ));
    $adTypesProperty = get_terms(array(
        "taxonomy" => "adTypeProperty",
        "hide_empty" => true,
    ));
?>
<div class="searchBar">
    <form role="search" action="" method="get" id="AdSearchBar">
        <input type="hidden" name="s">
        <input type="hidden" name="post_type" value="re-ad">

        <div class="formOneLine">
            <div class="cellInput">
                <label for="typeAd">Type d'annonce</label>
                <select name="typeAd" id="typeAd">
                    <?php
                    foreach($adTypesAd as $adTypeAd) { ?>
                        <option value="<?= $adTypeAd->slug; ?>" <?= isset($_GET["typeAd"]) && $_GET["typeAd"] === $adTypeAd->slug?"selected":''; ?>><?= $adTypeAd->name; ?></option>                 
                    <?php }
                    ?>
                </select>
            </div>

            <div class="cellInput">
                <label for="typeProperty">Type de bien</label>
                <select name="typeProperty" id="typeProperty">
                    <?php
                    foreach($adTypesProperty as $adTypeProperty) { ?>
                        <option value="<?= $adTypeProperty->slug; ?>" <?= isset($_GET["typeProperty"]) && $_GET["typeProperty"] === $adTypeProperty->slug?"selected":''; ?>><?= $adTypeProperty->name; ?></option>                 
                    <?php }
                    ?>
                </select>
            </div>

            <div class="cellInput">
                <label for="minSurface">Surface minimale</label>
                <input type="number" name="minSurface" id="minSurface" value="<?= isset($_GET["minSurface"])?intval($_GET["minSurface"]):'0'; ?>">
            </div>
            <div class="cellInput">
                <label for="maxSurface">Surface maximale</label>
                <input type="number" name="maxSurface" id="maxSurface" value="<?= isset($_GET["maxSurface"])?intval($_GET["maxSurface"]):'100'; ?>">
            </div>

            <div class="cellInput">
                <label for="minPrice">Prix minimum</label>
                <input type="number" name="minPrice" id="minPrice" value="<?= isset($_GET["minPrice"])?intval($_GET["minPrice"]):'0'; ?>">
            </div>
            <div class="cellInput">
                <label for="maxPrice">Prix maximum</label>
                <input type="number" name="maxPrice" id="maxPrice" value="<?= isset($_GET["maxPrice"])?intval($_GET["maxPrice"]):'250000'; ?>">
            </div>

            <div class="cellInput">
                <label for="city">Ville</label>
                <input type="text" name="city" id="city" <?= isset($_GET["city"])?'value="'.sanitize_text_field($_GET["city"]).'"':''; ?>>
            </div>
            <div class="cellInput">
                <label for="radius">Rayon de la recherche</label>
                <input type="number" name="radius" id="radius" value="<?= isset($_GET["radius"])?intval($_GET["radius"]):'10'; ?>">
            </div>
        </div>
        
        <input type="submit" value="Rechercher">
    </form>
</div>