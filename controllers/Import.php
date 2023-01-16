<?php
require_once(ABSPATH . "wp-includes/pluggable.php"); //Sinon erreur cannot modify header. A vérifier si toujours utile
require_once(ABSPATH . "wp-admin/includes/image.php"); //Pour importer des images

class Import {

    public function showPage() {
        $dirPath = plugin_dir_path(__DIR__)."import";
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
        $optionsImports = get_option(PLUGIN_RE_NAME."OptionsImports");        
        $addressePrecision = $optionsImports["addressPrecision"];
        foreach($arrayAds as $ad) {
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
            wp_set_post_terms($adWPId, sanitize_text_field($adData["typeProperty"]), "adTypeProperty");

            wp_set_post_terms($adWPId, sanitize_text_field($adData["typeAd"]), "adTypeAd");

            wp_set_post_terms($adWPId, "Disponible", "adAvailable"); //check ?
            
            /* METAS */
            update_post_meta($adWPId, "adFurnished", intval($adData["furnished"]));
            update_post_meta($adWPId, "adElevator", intval($adData["elevator"]));
            update_post_meta($adWPId, "adCellar", intval($adData["cellar"]));
            update_post_meta($adWPId, "adTerrace", intval($adData["terrace"]));
            update_post_meta($adWPId, "adRefAgency", sanitize_text_field($adData["refagency"]));
            update_post_meta($adWPId, "adPrice", floatval($adData["price"]));
            update_post_meta($adWPId, "adFees", floatval($adData["fees"]));
            update_post_meta($adWPId, "adSurface", floatval($adData["surface"]));
            update_post_meta($adWPId, "adLandSurface", floatval($adData["landsurface"]));
            update_post_meta($adWPId, "adNbRooms", intval($adData["nbrooms"]));
            update_post_meta($adWPId, "adNbBedrooms", intval($adData["nbbedrooms"]));
            update_post_meta($adWPId, "adNbBathrooms", intval($adData["nbbathrooms"]));
            update_post_meta($adWPId, "adNbWaterRooms", intval($adData["nbwaterrooms"]));
            update_post_meta($adWPId, "adNbWC", intval($adData["nbwc"]));
            /*$url = plugin_dir_url(__DIR__)."includes/php/getAddressData.php?query=".$adData["address"]."&import";
            $addressData = json_decode(wp_remote_retrieve_body(wp_remote_get($url)), true)[0];
            update_post_meta($adWPId, "adAddress", sanitize_text_field($addressData["address"]));
            update_post_meta($adWPId, "adLatitude", sanitize_text_field($addressData["coordinates"][1]));
            update_post_meta($adWPId, "adLongitude", sanitize_text_field($addressData["coordinates"][0]));*/
                        
            update_post_meta($adWPId, "adAddress", $adData["address"]);
            update_post_meta($adWPId, "adLatitude", $adData["latitude"]);
            update_post_meta($adWPId, "adLongitude", $adData["longitude"]);
            update_post_meta($adWPId, "adPc", $adData["pc"]);
            update_post_meta($adWPId, "adCity", $adData["city"]);
            update_post_meta($adWPId, "adDataMap", array("lat" => $adData["latitude"], "long" => $adData["longitude"], "zoom" => 16, "circ" => 10)); //Pas de verif pour savoir si ville ou non

            update_post_meta($adWPId, "adIdAgent", intval($adData["idagent"]));
            update_post_meta($adWPId, "adFloor", intval($adData["floor"]));
            update_post_meta($adWPId, "adNbFloors", intval($adData["nbfloors"]));
            update_post_meta($adWPId, "adYear", intval($adData["year"]));
            update_post_meta($adWPId, "adTypeHeating", sanitize_text_field($adData["typeheating"]));
            update_post_meta($adWPId, "adTypeKitchen", sanitize_text_field($adData["typekitchen"]));
            update_post_meta($adWPId, "adNbBalconies", sanitize_text_field($adData["nbbalconies"]));
            update_post_meta($adWPId, "adDPE", intval($adData["dpe"]));
            update_post_meta($adWPId, "adGES", intval($adData["ges"]));
            
            update_post_meta($adWPId, "adShowMap", sanitize_text_field($addressePrecision));
            
            
            /* PICTURES */
            SELF::setPictureProperty($adWPId, $adData["thumbnail"], true); //Pour la miniature
            foreach($adData["pictures"] as $URLPicture) {
                SELF::setPictureProperty($adWPId, $URLPicture);
            }
        }
    }
    
    public function widgetImport() {
        wp_add_dashboard_widget(PLUGIN_RE_NAME."widgetImport", "Importer les annonces", array($this, "showPage"));
    }
    
    
    private static function setPictureProperty($adWPId, $imgURL, $thumbnail=false) {
       $optionsImports = get_option(PLUGIN_RE_NAME."OptionsImports");
       $dirSavesPath = ABSPATH.$optionsImports["dirSavesPath"];
       $propertyImagesEmpty = true;
       $propertyImages = get_post_meta($adWPId, "adImages", true);
       $alreadyThisImage = false;
       if(!empty($propertyImages) && $propertyImages !== false) { //On vérifie s'il n'y a pas des images inscrites dans la BDD pour la galerie de la propriété
           $propertyImagesEmpty = false;

           $propertyImagesArray = explode(";", $propertyImages);
           foreach($propertyImagesArray as $propertyImage) { //On vérifie qu'on a pas déjà l'image
               if(get_post($propertyImage) !== null && get_post($propertyImage)->post_content === $imgURL) { //Vérifier si ça ne vient pas de la même URL. Remplacer par fonction qui compare vraiment l'image ?
                   $alreadyThisImage = true;
                   break;
               }
           }
       }

       if($alreadyThisImage === false) { //Si on a pas l'image, on la récupère et on l'enregistre
           $img = wp_remote_get($imgURL);
           if(is_array($img) && !is_wp_error($img) && wp_remote_retrieve_response_code($img) === 200) { //si on a bien réussi à la récupérer
               $imgOrigin = $img["body"];
               list($widthP, $heightP) = getimagesizefromstring($imgOrigin); //On va la redimensionner et la convertir. Selon les paramètres définis
               $maxDim = $optionsImports["maxDim"]; //Parametrable
               if($widthP > $maxDim || $heightP > $maxDim) { //Si l'image est plus petit que $maxDim (px)
                   $ratio = $widthP/$heightP;
                   if($ratio > 1) {
                       $newWidth = $maxDim;
                       $newHeighteight = $maxDim/$ratio;
                   }else{
                       $newWidth = $maxDim*$ratio;
                       $newHeighteight = $maxDim;
                   }
               }else{
                   $newWidth = $widthP;
                   $newHeighteight = $heightP;
               }

               $src = imagecreatefromstring($imgOrigin);
               $dst = imagecreatetruecolor($newWidth, $newHeighteight);
               imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeighteight, $widthP, $heightP);
               imagedestroy($src);
               $fileName = wp_unique_filename($dirSavesPath, "propertyPicture$adWPId.jpg");
               $pathImg = $dirSavesPath.$fileName;
               imagejpeg($dst, $pathImg, 85); //Quality à rajouter en param ?
               imagedestroy($dst);

               $uploadThumbnail = wp_upload_bits($fileName, null, @file_get_contents($pathImg)); //On l'enregistre dans la BDD
               unlink($pathImg);
               if(!$uploadThumbnail["error"]) {
                   $attachment = array(
                       "post_author" 	=> 1,
                       "post_mime_type" => "image/jpeg",
                       "post_parent"    => $adWPId,
                       "post_title"     => $fileName,
                       "post_content"   => sanitize_url($imgURL),
                       "post_status"    => "inherit"
                   );
                   $attachmentId = wp_insert_attachment($attachment, $uploadThumbnail["file"], $adWPId, true);

                   if(is_numeric($attachmentId)) {
                       if($attachmentData = wp_generate_attachment_metadata($attachmentId, $uploadThumbnail["file"])) {
                           wp_update_attachment_metadata($attachmentId, $attachmentData);
                       }else{
                           /*SELF::addLog("Impossible de mettre à jour les metadonnées de l'image $imgURL");
                           SELF::$errorAds++;*/
                       }

                       if($thumbnail) {
                           if(!set_post_thumbnail($adWPId, $attachmentId)) {
                               /*SELF::addLog("Impossible d'ajouter la miniature à l'annonce $adWPId (id BDD)");
                               SELF::$errorAds++;*/
                           }
                       }
                       if($propertyImagesEmpty === false) {
                           $attachmentId = $propertyImages.";".$attachmentId;
                       }
                       if(!update_post_meta($adWPId, "adImages", $attachmentId)) { //Important de mettre les ids dans un meta car sinon on peut retrouver seulement les photos uploadées AVEC le post. Ca ne marche pas si elles sont choisies dans la galerie.
                           /*SELF::addLog("Impossible d'ajouter l'image $imgURL à la galerie d'images de la propriété");
                           SELF::$errorAds++;*/
                           return false;
                       }

                   }else{
                       return false;
                   }

               }else{
                   /*SELF::addLog("Impossible d'enregistrer l'image $imgURL dans le dossier 'uploads' pour l'annonce $adWPId (id BDD) erreur : ".$uploadThumbnail["error"]);
                   SELF::$errorAds++;*/
                   return false;
               }
           }else{
               /*SELF::addLog("Impossible de récupérer l'image à l'adresse $imgURL erreur : ".wp_remote_retrieve_response_code($img));
               SELF::$errorAds++;*/
               return false;
           }
       }

    }
   
   
}