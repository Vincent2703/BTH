<?php
    require_once(preg_replace("/wp-content(?!.*wp-content).*/", '', __DIR__ )."wp-load.php");
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
    <form role="search" action="" method="get">
        <input type="hidden" name="s">
        <input type="hidden" name="post_type" value="re-ad">

        <div class="searchForm">
            <div class="mainSearchBarInputs">
                <div class="searchBarInput">
                    <label for="typeAd"><?php _e("Ad type", "retxtdom"); ?></label>
                    <select name="typeAd" id="typeAd" onchange="changeAdType(this)">
                        <?php
                        foreach($adTypesAd as $adTypeAd) { ?>
                            <option value="<?= $adTypeAd->slug; ?>" <?= isset($_GET["typeAd"]) && $_GET["typeAd"] === $adTypeAd->slug?"selected":''; ?>><?= $adTypeAd->name; ?></option>                 
                        <?php }
                        ?>
                    </select>
                </div>

                <div class="searchBarInput">
                    <label for="typeProperty"><?php _e("Property type", "retxtdom"); ?></label>
                    <select name="typeProperty" id="typeProperty">
                        <?php
                        foreach($adTypesProperty as $adTypeProperty) { ?>
                            <option value="<?= $adTypeProperty->slug; ?>" <?= isset($_GET["typeProperty"]) && $_GET["typeProperty"] === $adTypeProperty->slug?"selected":''; ?>><?= $adTypeProperty->name; ?></option>                 
                        <?php }
                        ?>
                    </select>
                </div>
                
                <div class="searchBarInput">
                    <label for="addressInput"><?php _e("City", "retxtdom"); ?></label>
                    <input type="text" name="city" id="addressInput" class="ui-autocomplete-input" autocomplete="off" size="15" placeholder="<?php _e("Ex: London", "retxtdom"); ?>" <?= isset($_GET["city"]) && !empty($_GET["city"])?'value="'.sanitize_text_field($_GET["city"]).'"':''; ?>>
                </div>

                <div class="searchBarInput">
                    <label for="radius"><?php _e("Search radius", "retxtdom"); ?></label>
                    <input type="number" name="radius" id="radius" value="<?= isset($_GET["radius"])?intval($_GET["radius"]):'10'; ?>">
                </div>
                
                <button type="button" id="filters" onclick="addFilters(this);"><?php _e("Filters", "retxtdom"); ?> +</button>
            </div>          
            
            <div class="compSearchBarInputs" style="display: none;">      
                <div class="pricesSurfacesInputs">
                    <div class="searchBarInput">
                        <label for="minPrice"><?php _e("Price", "retxtdom"); ?></label>
                        <input type="number" name="minPrice" id="minPrice" placeholder="<?php _e("min", "retxtdom"); ?>" value="<?= isset($_GET["minPrice"])&&intval($_GET["minPrice"])!==0?intval($_GET["minPrice"]):''; ?>">
                        <input type="number" name="maxPrice" id="maxPrice" placeholder="<?php _e("max", "retxtdom"); ?>" value="<?= isset($_GET["maxPrice"])&&intval($_GET["maxPrice"])!==0?intval($_GET["maxPrice"]):''; ?>">
                    </div>   
                
                    <div class="searchBarInput">
                        <label for="minSurface"><?php _e("Living space", "retxtdom"); ?></label>
                        <input type="number" name="minSurface" id="minSurface" placeholder="<?php _e("min", "retxtdom"); ?>" value="<?= isset($_GET["minSurface"])&&intval($_GET["minSurface"])!==0?intval($_GET["minSurface"]):''; ?>">
                        <input type="number" name="maxSurface" id="maxSurface" placeholder="<?php _e("max", "retxtdom"); ?>" value="<?= isset($_GET["maxSurface"])&&intval($_GET["maxSurface"])!==0?intval($_GET["maxSurface"]):''; ?>">
                    </div>
                </div>
                
                <div class="searchBarInput otherDetails">
                    <label for="furnished"><?php _e("Furnished", "retxtdom"); ?></label>
                    <input type="checkbox" name="furnished" id="furnished" <?php checked(isset($_GET["furnished"])&&intval($_GET["furnished"])!==0); ?>>
                    <label for="rooms"><?php _e("Number of rooms", "retxtdom"); ?></label>
                    <input type="number" name="nbRooms" id="rooms" value="<?= isset($_GET["nbRooms"])&&intval($_GET["nbRooms"])!==0?intval($_GET["nbRooms"]):''; ?>">
                    <label for="bedrooms"><?php _e("Number of bedrooms", "retxtdom"); ?></label>
                    <input type="number" name="nbBedrooms" id="bedrooms" value="<?= isset($_GET["nbBedrooms"])&&intval($_GET["nbBedrooms"])!==0?intval($_GET["nbBedrooms"]):''; ?>">
                    <label for="bathrooms"><?php _e("Number of bathrooms", "retxtdom"); ?></label>
                    <input type="number" name="nbBathrooms" id="bathrooms" value="<?= isset($_GET["nbBathrooms"])&&intval($_GET["nbBathrooms"])!==0?intval($_GET["nbBathrooms"]):''; ?>">
                </div>

            </div>
            
        </div>      
        <input type="submit" value="<?php _e("Search", "retxtdom"); ?>">
    </form>
</div>