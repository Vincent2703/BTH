<?php

class EditAd {
    public function addMetaBoxes() {
        add_meta_box( 
            "adBasicsMetaBox", //ID HTML
            "Renseignements basiques", //Display
            array($this, "displayAdBasicsMetaBox"), //Callback
            "ad", //Custom type
            "normal", //Location on the page
            "high" //Priority
        );
             
        add_meta_box( 
            "adAdvancedMetaBox", //ID HTML
            "Renseignements complémentaires", //Display
            array($this, "displayAdAdvancedDateMetaBox"), //Callback
            "ad", //Custom type
            "advanced", //Location on the page
            "high" //Priority
        );
    }
    
    function savePost($adId, $ad) {
        if($ad->post_type == "ad") {
            
            $ad->post_title = substr(sanitize_text_field($ad->postTitle), 0, 64);
            
            if(isset($_POST["adTypeProperty"]) && !ctype_space($_POST["adTypeProperty"])) {
                $this->saveTaxonomy($adId, "adTypeProperty");
            }
            if(isset($_POST["adTypeAd"]) && !ctype_space($_POST["adTypeAd"])) {
                $this->saveTaxonomy($adId, "adTypeAd");
            }
            if(isset($_POST["adAvailable"]) && $_POST["adAvailable"] === "Disponible") {
                $this->saveTaxonomyAdAvailable($adId, "Disponible");
            }else{
                $this->saveTaxonomyAdAvailable($adId, "Indisponible");
            }            
            
            if(isset($_POST["refAgency"]) && !ctype_space($_POST["refAgency"])) {
                update_post_meta($adId, "adRefAgency", sanitize_text_field($_POST["refAgency"]));
            }
            if(isset($_POST["price"]) && !ctype_space($_POST["price"])) {
                update_post_meta($adId, "adPrice", sanitize_text_field($_POST["price"]));
            }
            if(isset($_POST["fees"]) && !ctype_space($_POST["fees"])) {
                update_post_meta($adId, "adFees", sanitize_text_field($_POST["fees"]));
            }
            if(isset($_POST["showLabels"]) && !ctype_space($_POST["showLabels"])) {
                update_post_meta($adId, "adShowLabels", "OUI");        
            }
            if(isset($_POST["beforePrice"]) && !ctype_space($_POST["beforePrice"])) {
                update_post_meta($adId, "adBeforePrice", sanitize_text_field($_POST["beforePrice"]));
            }
            if(isset($_POST["afterPrice"]) && !ctype_space($_POST["afterPrice"])) {
                update_post_meta($adId, "adAfterPrice", sanitize_text_field($_POST["afterPrice"]));
            }
            if(isset($_POST["surface"]) && !ctype_space($_POST["surface"])) {
                update_post_meta($adId, "adSurface", sanitize_text_field($_POST["surface"]));
            }
            if(isset($_POST["landSurface"]) && !ctype_space($_POST["landSurface"])) {
                update_post_meta($adId, "adTotalSurface", sanitize_text_field($_POST["landSurface"]));
            }
            if(isset($_POST["nbRooms"]) && !ctype_space($_POST["nbRooms"])) {
                update_post_meta($adId, "adNbRooms", sanitize_text_field($_POST["nbRooms"]));
            }
            if(isset($_POST["nbBedrooms"]) && !ctype_space($_POST["nbBedrooms"])) {
                update_post_meta($adId, "adNbBedrooms", sanitize_text_field($_POST["nbBedrooms"]));
            }
            if(isset($_POST["nbBathrooms"]) && !ctype_space($_POST["nbBathrooms"])) {
                update_post_meta($adId, "adNbBathrooms", sanitize_text_field($_POST["nbBathrooms"]));
            }
            if(isset($_POST["nbWaterRooms"]) && !ctype_space($_POST["nbWaterRooms"])) {
                update_post_meta($adId, "adNbWaterRooms", sanitize_text_field($_POST["nbWaterRooms"]));
            }
            if(isset($_POST["nbWC"]) && !ctype_space($_POST["nbWC"])) {
                update_post_meta($adId, "adNbWC", sanitize_text_field($_POST["nbWC"]));
            }            
            
            if(isset($_POST["showMap"]) && !ctype_space($_POST["showMap"])) {
                update_post_meta($adId, "adShowMap", sanitize_text_field($_POST["showMap"]));
                if(isset($_POST["address"]) && $_POST["address"] !== '') {
                    update_post_meta($adId, "adAddress", sanitize_text_field($_POST["address"]));
                    if($_POST["showMap"] !== "no") {
                        $query = urlencode(addslashes(htmlentities(sanitize_text_field($_POST["address"]))));
                        if($_POST["showMap"] === "all") {
                            $zoom = 18;
                            $radiusCircle = 10;
                            $resultsResponse = wp_remote_get("https://api-adresse.data.gouv.fr/search/?q=".$query."&limit=1");
                        }else if($_POST["showMap"] === "onlyPC") {
                            $zoom = 14;
                            $radiusCircle = 50;
                            $resultsResponse = wp_remote_get("https://api-adresse.data.gouv.fr/search/?q=".$query."&type=municipality&limit=1");
                        }
                        if(wp_remote_retrieve_response_code($resultsResponse) === 200) {
                            $resultsBody = wp_remote_retrieve_body($resultsResponse);
                            $resultsArray = json_decode($resultsBody, true);

                            $coordinates = $resultsArray["features"][0]["geometry"]["coordinates"];
                            update_post_meta($adId, "adDataMap", array("lat" => $coordinates[1], "long" => $coordinates[0], "zoom" => $zoom, "circ" => $radiusCircle));

                            $PC = $resultsArray["features"][0]["properties"]["postcode"];
                            update_post_meta($adId, "adPC", $PC);

                            $city = $resultsArray["features"][0]["properties"]["city"];
                            update_post_meta($adId, "adCity", $city);
                        }
                    }
                }
            }
            if(isset($_POST["images"]) && !ctype_space($_POST["images"])) {
                update_post_meta($adId, "adImages", sanitize_text_field($_POST["images"]));
            }
            if(isset($_POST["agent"]) && ctype_digit(strval($_POST["agent"]))) {
                update_post_meta($adId, "adIdAgent", sanitize_text_field($_POST["agent"]));
            }
            if(isset($_POST["showAgent"])) {
                update_post_meta($adId, "adShowAgent", "OUI");
            }else{
                update_post_meta($adId, "adShowAgent", "NON");
            }
  
                       
            if(isset($_POST["labels"]) && !ctype_space($_POST["labels"])) {
                update_post_meta($adId, "adLabels", sanitize_text_field($_POST["labels"]));
            }
            
            
            if(isset($_POST["floor"]) && !ctype_space($_POST["floor"])) {
                update_post_meta($adId, "adFloor", sanitize_text_field($_POST["floor"]));
            }
            if(isset($_POST["nbFloors"]) && !ctype_space($_POST["nbFloors"])) {
                update_post_meta($adId, "adNbFloors", sanitize_text_field($_POST["nbFloors"]));
            }
            if(isset($_POST["furnished"]) && !ctype_space($_POST["furnished"])) {
                update_post_meta($adId, "adFurnished", "OUI");
            }
            if(isset($_POST["year"]) && !ctype_space($_POST["year"])) {
                update_post_meta($adId, "adYear", sanitize_text_field($_POST["year"]));
            }
            if(isset($_POST["typeHeating"]) && !ctype_space($_POST["typeHeating"])) {
                update_post_meta($adId, "adTypeHeating", sanitize_text_field($_POST["typeHeating"]));
            }
            if(isset($_POST["typeKitchen"]) && !ctype_space($_POST["typeKitchen"])) {
                update_post_meta($adId, "adTypeKitchen", sanitize_text_field($_POST["typeKitchen"]));
            }
            if(isset($_POST["orientation"]) && !ctype_space($_POST["orientation"])) {
                update_post_meta($adId, "adOrientation", sanitize_text_field($_POST["orientation"]));
            }
            if(isset($_POST["nbBalconies"]) && !ctype_space($_POST["nbBalconies"])) {
                update_post_meta($adId, "adNbBalconies", sanitize_text_field($_POST["nbBalconies"]));
            }
            if(isset($_POST["elevator"]) && !ctype_space($_POST["elevator"])) {
                update_post_meta($adId, "adElevator", "OUI");
            }
            if(isset($_POST["cellar"]) && !ctype_space($_POST["cellar"])) {
                update_post_meta($adId, "adCellar", "OUI");
            }
            if(isset($_POST["terrace"]) && !ctype_space($_POST["terrace"])) {
                update_post_meta($adId, "adTerrace", "OUI");
            }
        }
    }
    
        
    function saveTaxonomy($postId, $taxonomyName) {
        /*if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
            return;
        }*/

        $taxonomy = sanitize_text_field($_POST[$taxonomyName]);

        if(!empty($taxonomy)) {

            $term = get_term_by("name", $taxonomy, $taxonomyName);
            if(!empty($term) && !is_wp_error($term)) {
                wp_set_object_terms($postId, $term->term_id, $taxonomyName, false);
            }
        }
    }
    
    private static function saveTaxonomyAdAvailable($postId, $state) {
        $taxonomyName = "adAvailable";
        if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
            return;
        }
        
        if($term = get_term_by("name", $state, $taxonomyName)) {
            wp_set_object_terms($postId, $term->term_id, $taxonomyName, false);
        }
        
    }
    
    public function displayAdBasicsMetaBox($ad) {
        $refAgency = esc_html(get_post_meta($ad->ID, "adRefAgency", true));
        $price = intval(get_post_meta($ad->ID, "adPrice", true));
        $fees = intval(get_post_meta($ad->ID, "adFees", true));
        $surface = esc_html(get_post_meta($ad->ID, "adSurface", true));
        $landSurface = esc_html(get_post_meta($ad->ID, "adTotalSurface", true));
        $nbRooms = intval(get_post_meta($ad->ID, "adNbRooms", true));
        $nbBedrooms = intval(get_post_meta($ad->ID, "adNbBedrooms", true));
        $nbBathrooms = intval(get_post_meta($ad->ID, "adNbBathrooms", true));
        $nbWaterRooms = intval(get_post_meta($ad->ID, "adNbWaterRooms", true));
        $nbWC = intval(get_post_meta($ad->ID, "adNbWC", true));
        $address = esc_html(get_post_meta($ad->ID, "adAddress", true));
        $showMap = esc_html(get_post_meta($ad->ID, "adShowMap", true));
        $images = esc_html(get_post_meta($ad->ID, "adImages", true));
        $allAgents = get_posts(array("post_type" => "agent"));
        $agentSaved = esc_html(get_post_meta($ad->ID, "adAgent", true));
        $showAgent = esc_html(get_post_meta($ad->ID, "adShowAgent", true));
        ?>
        <div class="adBasics">
            <div id="refAgency">
                <div class="text">
                    <label>Référence de l'annonce<span id="generateRef" onclick="document.getElementById('refAgencyInput').value = <?= $ad->ID;?>;">Générer une référence</span></label>
                    <input type="text" name="refAgency" id="refAgencyInput" placeholder="Ex : A-123" value="<?= $refAgency; ?>">
                </div>
            </div>
            <div id="prices">
                <div class="text">
                    <label>Prix du bien</label>
                    <input type="number" name="price" id="priceInput" placeholder="Ex : 180000" value="<?= $price; ?>" required>
                </div>
                <div class="text">
                    <label>Montant des charges</label>
                    <input type="number" name="fees" id="feesInput" placeholder="Ex : 85" value="<?= $fees; ?>" required>
                </div>
            </div>
            <div id="surfaces">
                <div class="text">
                    <label>Surface habitable (m²)</label>
                    <input type="number" name="surface" id="surfaceInput" placeholder="Ex : 90" value="<?= $surface; ?>"  required>
                </div>
                <div class="text">
                    <label>Surface du terrain (m²)</label>
                    <input type="number" name="landSurface" id="landSurfaceInput" placeholder="Ex : 90" value="<?= $landSurface; ?>">
                </div>
            </div>
            <div id="address">
                <div class="text">
                    <label>Adresse du bien</label>
                    <input type="text" name="address" id="addressInput" autocomplete="off" placeholder="Ex : 123 rue de Grenoble 75002 Paris" value="<?= $address; ?>" required>
                </div>             

                <div class="radio">
                    <input type="radio" name="showMap" id="map1" value="no" <?=($showMap==="no")?"checked":NULL; //remplacer par la fonction wp checked()?> required><label for="map1">Ne pas afficher l'adresse</label>
                    <input type="radio" name="showMap" id="map2" value="onlyPC" <?=($showMap==="onlyPC"||!$showMap)?"checked":NULL;?> required><label for="map2">Afficher le code postal et la ville</label>
                    <input type="radio" name="showMap" id="map3" value="all" <?=($showMap==="all")?"checked":NULL;?> required><label for="map3">Afficher l'adresse complète</label>
                </div>
            </div>
            <div id="nbRooms">
                <div class="text">
                    <label>Nombre de pièces</label>       
                    <input type="number" name="nbRooms" id="nbRoomsInput" placeholder="Nombre de pièces" value="<?= $nbRooms; ?>" required>
                </div>
            </div>
            <div id="otherRooms">
                <div class="text">
                    <label>Nombre de chambres</label>
                    <input type="number" name="nbBedrooms" id="nbBedroomsInput" placeholder="Nombre de chambres" value="<?= $nbBedrooms; ?>" required>

                    <label>Nombre de salles de bain</label>
                    <input type="number" name="nbBathrooms" id="nbBathroomsInput" placeholder="Nombre de salles de bain" value="<?= $nbBathrooms; ?>" required>
                </div>
                <div class="text">
                    <label>Nombre de salles d'eau</label>
                    <input type="number" name="nbWaterRooms" id="nbWaterRoomsInput" placeholder="Nombre de salles d'eau" value="<?= $nbWaterRooms; ?>" required>

                    <label>Nombre de toilettes</label>
                    <input type="number" name="nbWC" id="nbWCInput" placeholder="Nombre de toilettes" value="<?= $nbWC; ?>" required>
                </div>
            </div>
            <div id="agent">
                <div class="text">
                    <label>Agent lié à l'annonce</label>
                    <select name="agent" id="agents" onclick="reloadAgents();">
                        <?php
                            foreach($allAgents as $agent) {
                                $nameAgent = get_the_title($agent);
                                $idAgent = $agent->ID;
                                ?>
                                <option value="<?= $idAgent; ?>" <?=($idAgent==$agentSaved)?"selected":NULL;?>><?= $nameAgent; ?></option>
                                <?php
                            }
                        ?>
                    </select>
                </div>
                <div class="select">
                    <input type="checkbox" id="showAgent" name="showAgent" <?=($showAgent==="OUI")?"checked":NULL;?>>
                    <label for="showAgent">Publier le contact de l'agent</label>
                    <a target="_blank" href="post-new.php?post_type=agent">Ajouter un agent</a>
                </div>
            </div>
            <div id="addPictures" class="radio">
                <a href="#" id="insertAdPictures" class="button">Ajouter des photos</a>
                <input type="hidden" name="images" id="images" value="<?= $images; ?>">
            </div>
                <div id="showPictures" class="radio">
                    <?php if(!is_null($images)) {
                        $ids = explode(';', $images);
                        foreach ($ids as $id) {
                            echo wp_get_attachment_image($id, array(150, 150), false, array("class" => "imgAd"));
                        }
                    }?>
            </div>
        </div>
        <?php
    }
      
    public function displayAdAdvancedDateMetaBox($ad) {
        $showLabels = esc_html(get_post_meta($ad->ID, "adShowLabels", true));
        $beforePrice = esc_html(get_post_meta($ad->ID, "adBeforePrice", true));
        $afterPrice = esc_html(get_post_meta($ad->ID, "adAfterPrice", true));
        $floor = intval(get_post_meta($ad->ID, "adFloor", true));
        $nbFloors = intval(get_post_meta($ad->ID, "adNbFloors", true));
        $furnished = esc_html(get_post_meta($ad->ID, "adFurnished", true));
        $year = intval(get_post_meta($ad->ID, "adYear", true));
        $typeHeating = esc_html(get_post_meta($ad->ID, "adTypeHeating", true));
        $typeKitchen = esc_html(get_post_meta($ad->ID, "adTypeKitchen", true));
        //$orientation = intval(get_post_meta($ad->ID, "adOrientation", true));
        $nbBalconies = intval(get_post_meta($ad->ID, "adNbBalconies", true));
        $elevator = esc_html(get_post_meta($ad->ID, "adElevator", true));
        $cellar = esc_html(get_post_meta($ad->ID, "adCellar", true));
        $terrace = esc_html(get_post_meta($ad->ID, "adTerrace", true));
        ?>
                    <div id="labelsActivation">
                <label class="switch">
                    <input type="checkbox" id="showLabels" name="showLabels" <?=($showLabels==="OUI")?"checked":NULL;?>>
                    <span class="slider"></span>
                </label>
                <label for="showLabels">&#160;Label(s)</label>
            </div>
            <div id="labels">
                <div class="text">
                    <label>Label avant le prix</label>
                    <input type="text" name="beforePrice" id="beforePriceInput" placeholder="Ex : A partir de" value="<?= $beforePrice; ?>">
                </div>
                <div class="text">
                    <label>Label après le prix</label>
                    <input type="text" name="afterPrice" id="afterPriceInput" placeholder="Ex : /mois" value="<?= $afterPrice; ?>">
                </div>
            </div>
            <label>Etage</label>
            <input type="number" name="floor" id="floorInput" placeholder="Etage" value="<?= $floor; ?>" required>
            <label>Nombre d'étages</label>
            <input type="number" name="nbFloors" id="nbFloorsInput" placeholder="Nombre d'étages" value="<?= $nbFloors; ?>" required>
            <label class="switch">
                <input type="checkbox" id="furnishedInput" name="furnished" <?=($furnished==="OUI")?"checked":NULL;?>>
                <span class="slider"></span>
            </label>   
            <label for="furnished">Meublé</label>
            <label>Année de construction</label>
            <input type="number" name="year" id="yearInput" placeholder="Année de construction" value="<?= $year; ?>" required>
            <label>Type de chauffage</label>
            <select name="typeHeating">
                <option value="0" <?=($typeHeating==0)?"selected":NULL;?>>Ne pas renseigner</option>
                <option value="8704" <?=($typeHeating==8704)?"selected":NULL;?>>
                    Gaz individuel</option>
                <option value="4608" <?=($typeHeating==4608)?"selected":NULL;?>>
                    Gaz collectif</option>
                <option value="9216" <?=($typeHeating==9216)?"selected":NULL;?>>
                    Fuel individuel</option>
                <option value="5120" <?=($typeHeating==5120)?"selected":NULL;?>>
                    Fuel collectif</option>
                <option value="10240" <?=($typeHeating==10240)?"selected":NULL;?>>
                    Electrique individuel</option>
                <option value="6144" <?=($typeHeating==6144)?"selected":NULL;?>>
                    Electrique collectif</option>
            </select>
            <label>Type de cuisine</label>
            <select name="typeKitchen">
                <option value="1" <?=($typeKitchen==1)?"selected":NULL;?>>Aucune</option>
                <option value="2" <?=($typeKitchen==2)?"selected":NULL;?>>Américaine</option>
                <option value="6" <?=($typeKitchen==6)?"selected":NULL;?>>Américaine équipée</option>
                <option value="3" <?=($typeKitchen==3)?"selected":NULL;?>>Séparée</option>
                <option value="7" <?=($typeKitchen==7)?"selected":NULL;?>>Séparée équipée</option>
                <option value="4" <?=($typeKitchen==4)?"selected":NULL;?>>Industrielle</option>
                <option value="5" <?=($typeKitchen==5)?"selected":NULL;?>>Kitchenette</option>
                <option value="8" <?=($typeKitchen==8)?"selected":NULL;?>>Kitchenette équipée</option>
            </select>
            <!--<label>Orientation</label>
            <select name="orientation">
                <option value="0" <?//=($orientation==0)?"selected":NULL;?>>Ne pas renseigner</option>
                <option value="south" <?//=($orientation==="south")?"selected":NULL;?>>Sud</option>
                <option value="east" <?//=($orientation=="east")?"selected":NULL;?>>Est</option>
                <option value="west" <?//=($orientation=="west")?"selected":NULL;?>>Ouest</option>
                <option value="north" <?//=($orientation=="north")?"selected":NULL;?>>Nord</option>
            </select>-->
            <label>Nombre de balcons</label>
            <input type="number" name="nbBalconies" id="nbBalconiesInput" placeholder="Nombre de balcons" value="<?= $nbBalconies; ?>" required>
            <label class="switch">
                <input type="checkbox" id="elevatorInput" name="elevator" <?=($elevator==="OUI")?"checked":NULL;?>>
                <span class="slider"></span>
            </label>   
            <label for="elevator">Ascenseur</label>
            <label class="switch">
                <input type="checkbox" id="cellarInput" name="cellar" <?=($cellar==="OUI")?"checked":NULL;?>>
                <span class="slider"></span>
            </label>    
            <label for="cellar">Cave</label>
            <label class="switch">
                <input type="checkbox" id="terraceInput" name="terrace" <?=($terrace==="OUI")?"checked":NULL;?>>
                <span class="slider"></span>
            </label>   
            <label for="terrace">Terrasse</label>

        <?php
    }
    
}
?>