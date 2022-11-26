<?php
require_once(ABSPATH . "wp-includes/pluggable.php"); //Sinon erreur cannot modify header. A vérifier si toujours utile
require_once(ABSPATH . "wp-admin/includes/image.php"); //Pour importer des images

class Import {

    public function showPage() {
        $optionsImports = get_option(PLUGIN_RE_NAME."OptionsImports");        
        $dirPath = ABSPATH.$optionsImports["dirImportPath"];
        $extAccepted = array(".zip", ".xml");
        ?>
        <div class="wrap">
            <form action="" method="post" enctype="multipart/form-data">
                <input type="file" name="file" accept="<?=implode(', ',$extAccepted);?>">
                <p>
                    <input type="submit" name="submitImport" class="button button-primary" value="Importer les annonces">
                    <input type="checkbox" id="publishAds" name="publishAds" checked>
                    <label for="publishAds">Publier directement les annonces</label>                    
                </p>      
            </form>
        </div>
        <?php
        if(is_admin()) {
            if(isset($_POST["submitImport"])) {
                if(!is_dir($dirPath)) {
                    echo $dirPath;
                    mkdir($dirPath);
                }
                $filePath = $dirPath.basename(sanitize_file_name($_FILES["file"]["name"]));
                $uploadOk = true;
                $imageFileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                if(!in_array(".$imageFileType", $extAccepted)) { 
                    echo "Seulement les fichiers ".implode(', ',$extAccepted)." sont autorisés.";
                    $uploadOk = false;
                }

                if($uploadOk && move_uploaded_file($_FILES["file"]["tmp_name"], $filePath)) {
                    $arrayAds = SELF::XMLToArray($filePath);
                    SELF::arrayToAds($arrayAds);
                }else{
                    echo "<br/>Une erreur est survenue lors de l'envoi.";
                }                          
            }
        }
                
    }
    
    private static function XMLToArray($filePath) {
        if(file_exists($filePath)) {
            $xml = simplexml_load_file($filePath);
            if($xml != false) {
                return json_decode(json_encode($xml), true);
            }
        }
       
    }
    
    private static function arrayToAds($arrayAds) {
        foreach($arrayAds as $ad) {
            print_r($ad);
            $adData = $ad["adData"];
            $agentData = $ad["agentData"];
            $agencyData = $ad["agencyData"];
            //Verifier si l'annonce n'existe pas déjà
            $post = array( //Array création du post
                "post_title" 	=> $adData["title"],
                "post_author" 	=> 1, //admin
                "post_content" 	=> $adData["description"],
                "post_type" 	=> "re-ad",
            );
            if(isset($_POST["publishAds"])) {//Si l'utilisateur a décidé publier directement les annonces
                $post["post_status"] = "publish";
            }else{
                $post["post_status"] = "private";
            }
            $adWPId = wp_insert_post($post, true); //On crée l'annonce et on obtient l'ID
            
            /* TERMS */
            wp_set_post_terms($adWPId, $adData["typeProperty"], "adTypeProperty");

            wp_set_post_terms($adWPId, $adData["typeAd"], "adTypeAd");

            wp_set_post_terms($adWPId, "Disponible", "adAvailable"); //check ?
            
            /* METAS */
            update_post_meta($adWPId, "adFurnished", $adData["furnished"]); //rajouter sanitize
            update_post_meta($adWPId, "adElevator", $adData["elevator"]);
            update_post_meta($adWPId, "adCellar", $adData["cellar"]);
            update_post_meta($adWPId, "adTerrace", $adData["terrace"]);
            update_post_meta($adWPId, "adRefAgency", $adData["refagency"]);
            update_post_meta($adWPId, "adPrice", $adData["price"]);
            update_post_meta($adWPId, "adFees", $adData["fees"]);
            update_post_meta($adWPId, "adSurface", $adData["surface"]);
            update_post_meta($adWPId, "adTotalSurface", $adData["totalsurface"]);
            update_post_meta($adWPId, "adNbRooms", $adData["nbrooms"]);
            update_post_meta($adWPId, "adNbBedrooms", $adData["nbbedrooms"]);
            update_post_meta($adWPId, "adNbBathrooms", $adData["nbbathrooms"]);
            update_post_meta($adWPId, "adNbWaterRooms", $adData["nbwaterrooms"]);
            update_post_meta($adWPId, "adNbWC", $adData["nbwc"]);
            update_post_meta($adWPId, "adAddress", $adData["address"]);
            update_post_meta($adWPId, "adLatitude", $adData["latitude"]);
            update_post_meta($adWPId, "adLongitude", $adData["longitude"]);
            update_post_meta($adWPId, "adPc", $adData["pc"]);
            update_post_meta($adWPId, "adCity", $adData["city"]);
            update_post_meta($adWPId, "adIdAgent", $adData["idagent"]);
            update_post_meta($adWPId, "adFloor", $adData["floor"]);
            update_post_meta($adWPId, "adNbFloors", $adData["nbfloors"]);
            update_post_meta($adWPId, "adYear", $adData["year"]);
            update_post_meta($adWPId, "adTypeHeating", $adData["typeheating"]);
            update_post_meta($adWPId, "adTypeKitchen", $adData["typekitchen"]);
            update_post_meta($adWPId, "adNbBalconies", $adData["nbbalconies"]);
            update_post_meta($adWPId, "adDpe", $adData["dpe"]);
            update_post_meta($adWPId, "adGes", $adData["ges"]);
            
            /* PICTURES */
        }
    }
    
    public function widgetImport() {
        wp_add_dashboard_widget(PLUGIN_RE_NAME."widgetImport", "Importer les annonces", array($this, "showPage"));
    }
   
   
}