<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
class REALM_Import {
    private static $importOptions; //RAJOUTER TABLEAU EXPORTS A IMPORTER
    private static $dirPathImports;
    private static $dirPathExports;
    private static $extAccepted;
    private static $files;
    
    public function __construct() {
        SELF::$importOptions = get_option(PLUGIN_RE_NAME."OptionsImports");
        SELF::$dirPathImports = PLUGIN_RE_PATH."imports/";
        SELF::$dirPathExports = PLUGIN_RE_PATH."exports/";
        SELF::$extAccepted = array(".zip", ".xml");
        SELF::$files = array_diff(scandir(PLUGIN_RE_PATH."exports"), array('.', ".."));
        usort(SELF::$files, function($a, $b) {    
            return filemtime(PLUGIN_RE_PATH."exports/$b") - filemtime(PLUGIN_RE_PATH."exports/$a");
        });
    }

    public function showPage() { 
        $postType = get_current_screen()->post_type;
        $base = get_current_screen()->base;
        if(isset($_GET["import"]) && preg_match("/.+\.xml$/", $_GET["import"]) && isset($_GET["nonceSecurity"]) && wp_verify_nonce($_GET["nonceSecurity"], "importAds")) {
            SELF::startImport(SELF::$dirPathExports.$_GET["import"]);
        }
        ?>
        <div class="wrap">
            <h2><?php _e("Import the ads", "retxtdom"); ?></h2>
            <form action="" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field("formImportAds", "nonceSecurity"); ?>
                <input type="file" name="file" accept="<?=implode(", ", SELF::$extAccepted);?>">
                <p>
                    <input type="submit" name="submitImport" class="button button-primary" value="<?php __("Import the ads", "retxtdom"); ?>">
                    <br />
                    <input type="checkbox" id="publishAds" name="publishAds" checked>
                    <label for="publishAds"><?php _e("Publish directly the ads", "retxtdom"); ?></label><br />        
                    <input type="checkbox" id="replaceAds" name="replaceAds" checked>
                    <label for="replaceAds"><?php _e("Replace the ads with the same reference", "retxtdom"); ?></label>          
                </p>      
            </form>
            <?php if($postType==="re-ad" && $base=PLUGIN_RE_NAME."import") { ?>
            <table>
                <thead>
                    <tr>
                        <th><?php _e("Date", "retxtdom"); ?></th>
                        <th><?php _e("Number of ads", "retxtdom"); ?></th>
                        <th><?php _e("Import", "retxtdom"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach(SELF::$files as $file) { 
                        $filePath = SELF::$dirPathExports.$file; ?>
                    <tr>
                        <td><?= date("Y-m-d h:i:s", filemtime($filePath)); ?></td>
                        <td><?= SELF::countAds($filePath); ?></td>
                        <td><span class="importLink" data-file="<?=$file;?>"><?php _e("Import", "retxtdom"); ?></span></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php } ?>
        </div>
        <?php
        if(is_admin()) {
            if(isset($_POST["submitImport"])) {
                if(!is_dir(SELF::$dirPathImports)) {
                    mkdir(SELF::$dirPathImports);
                }
                $filePath = SELF::$dirPathImports.basename(sanitize_file_name($_FILES["file"]["name"]));
                $uploadOk = true;
                $imageFileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                if(!in_array(".$imageFileType", SELF::$extAccepted)) { 
                    printf(__('Only the files with one of these extensions are accepted : (%1$s)', "retxtdom"), implode(", ", SELF::$extAccepted));
                    $uploadOk = false;
                }

                if($uploadOk && move_uploaded_file($_FILES["file"]["tmp_name"], $filePath)) {
                    self::startImport($filePath);
                    @unlink($filePath);
                }else{
                    _e("An error occurred while sending.", "retxtdom");
                }
                _e("Successful import", "retxtdom");
            }
        }
                
    }
    
    private static function XMLToArray($filePath) {
        if(file_exists($filePath)) {
            $xml = simplexml_load_file($filePath);
            if($xml != false) {
                return json_decode(json_encode($xml), true);
            }else{
                return false;
            }
        }
    }
    
    private static function arrayToAds($arrayAds) {
        foreach($arrayAds as $ad) {
            $adData = $ad["adData"];
            //$agentData = $ad["agentData"];
            //$agencyData = $ad["agencyData"];
            //Verifier si l'annonce n'existe pas déjà
            $createPost = true;
            if(isset($_POST["replaceAds"]) || isset($_GET["replaceAds"])) {
                $post = get_posts(array(
                        "post_type" => "re-ad",
                        "fields" => "ids",
                        "numberposts" => 999,
                        "meta_query" => array(
                            array(
                                "key" => "adRefAgency",
                                "value" => $adData["refAgency"]
                            )
                        )
                    )
                );
                  
                if(!empty($post)) {
                    $createPost = false;
                    $adWPId = $post[0];
                    $data = array(
                        "ID" => $adWPId,
                        "post_title" => $adData["title"],
                        "post_content" => $adData["description"],
                    );

                    wp_update_post($data);
                }
            }
            if($createPost) {
                $post = array( //Array création du post
                    "post_title" 	=> $adData["title"],
                    "post_author" 	=> 1, //admin
                    "post_content" 	=> $adData["description"],
                    "post_type" 	=> "re-ad",
                );
                if(isset($_POST["publishAds"]) || isset($_GET["publishAds"])) {//Si l'utilisateur a décidé publier directement les annonces
                    $post["post_status"] = "publish";
                }else{
                    $post["post_status"] = "private";
                }

                $adWPId = wp_insert_post($post, true); //On crée l'annonce et on obtient l'ID            
            }          
                        
            /* TERMS */
            wp_set_post_terms($adWPId, sanitize_text_field($adData["typeProperty"]), "adTypeProperty");

            wp_set_post_terms($adWPId, sanitize_text_field($adData["typeAd"]), "adTypeAd");

            wp_set_post_terms($adWPId, "Disponible", "adAvailable"); //check ?
            
            /* METAS */
            $arraykeysParsing = array_diff(array_keys($adData), ["agentData", "agencyData", "title", "description", "typeProperty", "typeAd", "adAvailable", "latitude", "longitude", "agentData"]);
            foreach($arraykeysParsing as $keyXML) {
                $keyMeta = "ad".ucfirst($keyXML);
                update_post_meta($adWPId, $keyMeta, sanitize_text_field($adData[$keyXML]));
            }
            
            //GPS
            update_post_meta($adWPId, "adDataMap", array("lat" => $adData["latitude"], "long" => $adData["longitude"], "zoom" => 16, "circ" => 1));
            update_post_meta($adWPId, "adLatitude", $adData["latitude"]);
            update_post_meta($adWPId, "adLongitude", $adData["longitude"]);
            
            /* PICTURES */
            if(!empty($adData["thumbnail"])) {
                SELF::setPictureProperty($adWPId, $adData["thumbnail"], true); //Pour la miniature
            }
            if(!empty($adData["pictures"][key($adData["pictures"])])) {
                foreach($adData["pictures"] as $URLPicture) {
                    SELF::setPictureProperty($adWPId, $URLPicture);
                }
            }
        }
    }
    
    public function widgetImport() {
        if(current_user_can("administrator")) {
            wp_add_dashboard_widget(PLUGIN_RE_NAME."widgetImport", __("Import the ads", "retxtdom"), array($this, "showPage"));
        }
    }
    
    private static function countAds($filePath) {
        $handle = fopen($filePath, 'r');
        $adsXML = fread($handle, filesize($filePath));
        fclose($handle);

        $ads = new SimpleXMLElement($adsXML);
        return $ads->count();
    }
    
    
    private static function setPictureProperty($adWPId, $imgURL, $thumbnail=false) {
       $dirImport = PLUGIN_RE_PATH."import";
       
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
               $maxDim = SELF::$importOptions["maxDim"]; //Parametrable
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
               $fileName = wp_unique_filename($dirImport, "propertyPicture$adWPId.jpg");
               $pathImg = $dirImport.$fileName;
               imagejpeg($dst, $pathImg, SELF::$importOptions["qualityPictures"]);
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
                   $attachmentData = wp_generate_attachment_metadata($attachmentId, $uploadThumbnail["file"]);
                   wp_update_attachment_metadata($attachmentId, $attachmentData);

                   if(is_numeric($attachmentId)) {
                       if($thumbnail) {
                           set_post_thumbnail($adWPId, $attachmentId);
                       }else{
                            $attachmentData = wp_generate_attachment_metadata($attachmentId, $uploadThumbnail["file"]);
                            wp_update_attachment_metadata($attachmentId, $attachmentData);
                            if($propertyImagesEmpty === false) {
                                $attachmentId = $propertyImages.";".$attachmentId;
                            }
                            update_post_meta($adWPId, "adImages", $attachmentId); //Important de mettre les ids dans un meta car sinon on peut retrouver seulement les photos uploadées AVEC le post. Ca ne marche pas si elles sont choisies dans la galerie.
                        }
                   }
               }
           }
       }
    }
    
    private static function startImport($filePath) {
        $arrayAds = SELF::XMLToArray($filePath);
        if($arrayAds != false) {
            SELF::arrayToAds($arrayAds);
        }else{
            _e("The file is not valid", "retxtdom");
        }
    }
    private static function checkQuotaImports() {
        $optionsImports = get_option(PLUGIN_RE_NAME."OptionsImports");
        $maxSavesImports = intval($optionsImports["maxSavesImports"]);
        $filesToDelete = array_slice(SELF::$files, $maxSavesImports-1);
        foreach($filesToDelete as $file) {
            SELF::deleteExport($file);
        }
        
        SELF::$files = array_diff(scandir(PLUGIN_RE_PATH."exports"), array('.', ".."));
        usort(SELF::$files, function($a, $b) {    
            return filemtime(SELF::$dirPathImports.$b) - filemtime(SELF::$dirPathImports.$a);
        });
    }
    
    private static function deleteExport($filePath) {
        unlink(SELF::$dirPathImports.$filePath); //Add check
    }
    
   
}