<?php
    require_once(preg_replace("/wp-content(?!.*wp-content).*/", '', __DIR__ )."wp-load.php");
    if(!defined("PLUGIN_RE_SEARCHBAR")) {
        exit;
    }
    $adTypesAd = get_terms(array(
        "taxonomy" => "adTypeAd",
        "hide_empty" => true,
    ));
    $adTypesProperty = get_terms(array(
        "taxonomy" => "adTypeProperty",
        "hide_empty" => true,
    ));
    
    $compSearchBarExtended = (isset($_GET["minPrice"]) && is_numeric($_GET["minPrice"])) || 
            (isset($_GET["maxPrice"]) && is_numeric($_GET["maxPrice"])) || 
            (isset($_GET["minSurface"]) && is_numeric($_GET["minSurface"])) || 
            (isset($_GET["maxSurface"]) && is_numeric($_GET["maxSurface"])) || 
            (isset($_GET["nbRooms"]) && is_numeric($_GET["nbRooms"])) || 
            (isset($_GET["nbBedrooms"]) && is_numeric($_GET["nbBedrooms"])) || 
            (isset($_GET["nbBathrooms"]) && is_numeric($_GET["nbBathrooms"])) ||
            isset($_GET["furnished"]) ||
            isset($_GET["outdoorSpace"]) ||
            isset($_GET["land"]) ||
            isset($_GET["elevator"]) ||
            isset($_GET["cellar"]) ||
            isset($_GET["garageparking"]);
            
?>
<div class="searchBar">
    <form role="search" action="<?= get_post_type_archive_link("re-ad"); ?>" method="get">
        <input type="hidden" name="s">
        <input type="hidden" name="post_type" value="re-ad">
        <input type="hidden" name="city" value="<?=isset($_GET["city"])?esc_attr($_GET["city"]):'';?>">
        <input type="hidden" name="postCode" value="<?=isset($_GET["postCode"])?esc_attr($_GET["postCode"]):'';?>">
        <input type="hidden" name="lat" value="<?=isset($_GET["lat"])?esc_attr($_GET["lat"]):'';?>">
        <input type="hidden" name="long" value="<?=isset($_GET["long"])?esc_attr($_GET["long"]):'';?>">
        
        <button id="btnSearchBarSmallScreens" type="button"><?php _e("Search ads", "retxtdom"); ?>&nbsp;<span class="dashicons dashicons-arrow-down-alt2"></span></button>
        <button id="btnCloseSearchBarSmallScreens" type="button"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
        
        <div class="searchForm">
            <div class="mainSearchBarInputs">
                <div class="searchBarInput typeAd">
                    <label for="typeAd"><?php _e("Type of ad", "retxtdom"); ?></label>
                    <select name="typeAd" id="typeAd">
                        <?php
                        if(empty($adTypesAd)) { ?>
                            <option disabled selected><?php _e("No ad posted", "retxtdom") ;?></option>
                        <?php }else{
                        foreach($adTypesAd as $adTypeAd) { ?>
                            <option value="<?= $adTypeAd->slug; ?>" <?php selected(isset($_GET["typeAd"]) && $_GET["typeAd"] === $adTypeAd->slug); ?>><?= sanitize_text_field($adTypeAd->name); ?></option>                 
                        <?php }
                        }
                        ?>
                    </select>
                </div>

                <div class="searchBarInput typeProperty">
                    <label for="typeProperty"><?php _e("Type of property", "retxtdom"); ?></label>
                    <select name="typeProperty" id="typeProperty">
                        <?php
                        if(empty($adTypesProperty)) { ?>
                            <option disabled selected><?php _e("No ad posted", "retxtdom") ;?></option>
                        <?php }else{
                        foreach($adTypesProperty as $adTypeProperty) { ?>
                            <option value="<?= esc_attr($adTypeProperty->slug); ?>" <?php selected(isset($_GET["typeProperty"]) && $_GET["typeProperty"] === $adTypeProperty->slug);?>><?= $adTypeProperty->name; ?></option>                 
                        <?php }
                        }
                        ?>
                    </select>
                </div>
                
                <div class="searchBarInput address">
                    <label for="addressInput"><?php _e("City and postcode", "retxtdom"); ?></label>
                    <?php printf(
                        '<input type="text" data-context="searchBar" data-nonce="%s" id="addressInput" class="ui-autocomplete-input" autocomplete="off" size="15" placeholder="%s" value="%s%s">',
                        esc_attr(wp_create_nonce("autocompleteAddress"), ENT_QUOTES),
                        esc_attr(__("Ex: London", "retxtdom"), ENT_QUOTES),
                        isset($_GET["city"]) && !empty(trim($_GET["city"]))?esc_attr(urldecode($_GET["city"])):'',
                        isset($_GET["postCode"]) && !empty(trim($_GET["postCode"]))?' '.esc_attr(urldecode($_GET["postCode"])):'',
                    ); ?>

                </div>

                <div id="searchBy" class="searchBarInput searchBy">
                    <label for="searchBySelect"><?php _e("Search by", "retxtdom");?></label>
                    <select id="searchBySelect" name="searchBy">
                        <option value="city" <?php selected(isset($_GET["searchBy"]) && $_GET["searchBy"] === "city"); ?>><?php _e("City", "retxtdom"); ?></option>
                        <option value="radius" <?php selected(isset($_GET["searchBy"]) && $_GET["searchBy"] === "radius"); ?>><?php _e("Radius", "retxtdom"); ?></option>
                    </select>                 
                </div>
                <div id="radiusInput" class="searchBarInput" <?= !isset($_GET["searchBy"]) || $_GET["searchBy"]==="city"?'style="display: none;"':''; ?>>
                    <label for="radius"><?php _e("Radius", "retxtdom"); ?></label>
                    <input type="number" name="radius" id="radius" value="<?= isset($_GET["radius"])?absint($_GET["radius"]):'10'; ?>">
                </div>
                <span class="searchBtn">
                    <input type="submit" value="<?php _e("Search", "retxtdom"); ?>" 
                        <?php disabled(
                                !isset($_GET["city"]) || empty(trim($_GET["city"])) ||
                                !isset($_GET["lat"]) || empty(trim($_GET["lat"])) ||
                                !isset($_GET["long"]) || empty(trim($_GET["long"]))
                            ); 
                        ?>
                    > 
                </span>
            </div>          
            <div class="filtersSearchBarInputs" <?= $compSearchBarExtended ?: 'style="display: none;"'; ?>>
                <div class="pricesSurfacesInputs">
                    <div class="searchBarInput">
                        <label for="minPrice"><?php _e("Price", "retxtdom"); ?></label>
                        <input type="number" min="0" name="minPrice" id="minPrice" placeholder="<?php _e("min", "retxtdom"); ?>" <?= $_GET["minPrice"] ?? '' ? 'value="'.absint($_GET["minPrice"]).'"' : '' ?>>
                        <input type="number" min="0" name="maxPrice" id="maxPrice" placeholder="<?php _e("max", "retxtdom"); ?>" <?= $_GET["maxPrice"] ?? '' ? 'value="'.absint($_GET["maxPrice"]).'"' : '' ?>>
                    </div>   
                
                    <div class="searchBarInput">
                        <label for="minSurface"><?php _e("Living space", "retxtdom"); ?></label>
                        <input type="number" min="0" name="minSurface" id="minSurface" placeholder="<?php _e("min", "retxtdom"); ?>" <?= $_GET["minSurface"] ?? '' ? 'value="'.absint($_GET["minSurface"]).'"' : '' ?>>
                        <input type="number" min="0" name="maxSurface" id="maxSurface" placeholder="<?php _e("max", "retxtdom"); ?>"<?= $_GET["maxSurface"] ?? '' ? 'value="'.absint($_GET["maxSurface"]).'"' : '' ?>>
                    </div>
                </div>
                
                <div class="searchBarInput rooms">
                    <label for="rooms"><?php _e("Rooms", "retxtdom"); ?></label>
                    <input type="number" name="nbRooms" min="0" max="99" id="rooms" <?= $_GET["nbRooms"] ?? '' ? 'value="'.absint($_GET["nbRooms"]).'"' : '' ?>>
                    <label for="bedrooms"><?php _e("Bedrooms", "retxtdom"); ?></label>
                    <input type="number" name="nbBedrooms" min="0" max="99" id="bedrooms" <?= $_GET["nbBedrooms"] ?? '' ? 'value="'.absint($_GET["nbBedrooms"]).'"' : '' ?>>
                    <label for="bathrooms"><?php _e("Bathrooms", "retxtdom"); ?></label>
                    <input type="number" name="nbBathrooms" min="0" max="99" id="bathrooms" <?= $_GET["nbBathrooms"] ?? '' ? 'value="'.absint($_GET["nbBathrooms"]).'"' : '' ?>>
                </div>
                <div class="searchBarInput propertyHas">
                    <div class="colPropertyHas">
                        <span class="propertyCharact">
                            <label for="furnished"><?php _e("Furnished", "retxtdom"); ?></label>
                            <input type="checkbox" name="furnished" id="furnished" <?php checked(isset($_GET["furnished"])&&$_GET["furnished"]==="on"); ?>>
                            <span class="checkMark"></span>
                        </span>
                        <span class="propertyCharact">
                            <label for="land"><?php _e("Land", "retxtdom"); ?></label>
                            <input type="checkbox" name="land" id="land" <?php checked(isset($_GET["land"])&&$_GET["land"]==="on"); ?>>
                            <span class="checkMark"></span>
                        </span>
                        <span class="propertyCharact">
                            <label for="cellar"><?php _e("Cellar", "retxtdom"); ?></label>
                            <input type="checkbox" name="cellar" id="cellar" <?php checked(isset($_GET["cellar"])&&$_GET["cellar"]==="on"); ?>>
                            <span class="checkMark"></span>
                        </span>
                    </div>
                    <div class="colPropertyHas">
                        <span class="propertyCharact">
                            <label for="outdoorSpace"><?php _e("Outdoor space", "retxtdom"); ?></label>
                            <input type="checkbox" name="outdoorSpace" id="outdoorSpace" <?php checked(isset($_GET["outdoorSpace"])&&$_GET["outdoorSpace"]==="on"); ?>>
                            <span class="checkMark"></span>
                        </span>
                        <span class="propertyCharact">
                            <label for="elevator"><?php _e("Elevator", "retxtdom"); ?></label>
                            <input type="checkbox" name="elevator" id="elevator" <?php checked(isset($_GET["elevator"])&&$_GET["elevator"]==="on"); ?>>
                            <span class="checkMark"></span>
                        </span>
                        <span class="propertyCharact">
                            <label for="garageparking"><?php _e("Garage/Parking", "retxtdom"); ?></label>
                            <input type="checkbox" name="garageparking" id="garageparking" <?php checked(isset($_GET["garageparking"])&&$_GET["garageparking"]==="on"); ?>>
                            <span class="checkMark"></span>
                        </span>
                    </div>
                </div>
                <span class="searchBtn">
                    <input type="submit" value="<?php _e("Search", "retxtdom"); ?>" 
                        <?php disabled(
                                !isset($_GET["city"]) || empty(trim($_GET["city"])) ||
                                !isset($_GET["lat"]) || empty(trim($_GET["lat"])) ||
                                !isset($_GET["long"]) || empty(trim($_GET["long"]))
                            ); 
                        ?>
                    > 
                </span>
            </div>
            <span id="filters">
                <span><?php _e("Filters", "retxtdom"); ?></span>
                <span class="dashicons dashicons-plus-alt"></span>
            </span>
        </div>      
    </form>
</div>