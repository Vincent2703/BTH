<?php

class Export {    
    
    public function showPage() {     
        $postType = get_current_screen()->post_type;
        $base = get_current_screen()->base;
        ?>
        <div class="wrap">
            <?php if($postType==="re-ad" && $base="bthexport") { ?>
                <h2>Exportez les annonces</h2>
            <?php } ?>
                <form action="" method="post">
                    <p>
                        <input type="submit" name="submitExport" class="button button-primary" value="Exporter">                
                        <input type="checkbox" id="onlyAvailable" name="onlyAvailable" checked>
                        <label for="onlyAvailable">Exporter uniquement les biens disponibles</label>
                    </p>
            </form>
            <p><a href="?downloadSave" class="button button-primary">Télécharger une sauvegarde</a></p>
        </div>
        <?php
        if(isset($_POST["submitExport"])) {
            SELF::startExport();
        }
    }
    
    public function widgetExport() {
        wp_add_dashboard_widget(PLUGIN_RE_NAME."widgetExport", "Exporter les annonces", array($this, "showPage"));
    }

    private static function getPreparedAds() {
        $optionsExports = get_option(PLUGIN_RE_NAME."OptionsExports");
        $optionsFees = get_option(PLUGIN_RE_NAME."OptionsFees");
        $fields = get_option(PLUGIN_RE_NAME."OptionsMapping");

        $args = array(
            "numberposts" => -1,
            "post_type" => "re-ad",
            "post_status" => "publish"
        );
        if(isset($_POST["onlyAvailable"])) {
            $args["tax_query"] = array(
                array(
                    "taxonomy" => "adAvailable",
                    "term" => "name",
                    "terms" => array("Disponible"),
                    "operator" => "EXISTS"
                )
            );
        }

        $ads = get_posts($args);
               
        $arrayAds = array();                

        if(!empty($ads)) {
            foreach($ads as $ad) {
                $metas = array_map(function($n) {return $n[0];}, get_post_meta($ad->ID));         
                $imagesIds = $metas["adImages"]; //On récupère les images
                $images = array();
                if(!is_null($imagesIds)) {
                    $ids = explode(';', $imagesIds); //Les IDs sont séparés par un ;
                    foreach ($ids as $id) {
                        array_push($images, wp_get_attachment_image_url($id, "large")); //Pour chaque image on récupère leur URL
                    }

                    $sizeImgs = count($images);
                    if($sizeImgs < 9) {
                        $images += array_fill($sizeImgs, 9-$sizeImgs, ""); //S'il y a moins de 10 images, le champ sera laissé vide dans le CSV
                    }
                }

                //Récupérer infos agent
                if($metas["adShowAgent"] === "on" && isset($metas["adAgent"]) && $agent = get_post($metas["adAgent"])) {
                    $agentEmail = get_post_meta($agent, "agentEmail", true);                 
                }else{
                    $agentEmail = "";
                }

                $arrayAd = [
                    1                                               => $optionsExports["idAgency"],
                    SELF::getFieldIdByName($fields, "title")        => html_entity_decode(get_the_title($ad, ENT_COMPAT, "UTF-8")),
                    SELF::getFieldIdByName($fields, "typeAd")       => get_the_terms($ad, "adTypeAd")[0]->name,
                    SELF::getFieldIdByName($fields, "typeProperty") => get_the_terms($ad, "adTypeProperty")[0]->name,
                    SELF::getFieldIdByName($fields, "description")  => html_entity_decode(get_the_content(null, null, $ad), ENT_COMPAT, "UTF-8"), 
                    SELF::getFieldIdByName($fields, "latitude")     => unserialize($metas["adDataMap"])["lat"],
                    SELF::getFieldIdByName($fields, "longitude")    => unserialize($metas["adDataMap"])["long"],
                    SELF::getFieldIdByName($fields, "agentEmail")   => $agentEmail,
                    SELF::getFieldIdByName($fields, "feesAgency")   => $optionsFees["feesUrl"],
                    300 => "1" //Précision GPS (?)
                ];

                $arrayAd[SELF::getFieldIdByName($fields, "thumbnail")] = $images[0];
                for($i=1; $i<=8; $i++) {
                    $arrayAd[SELF::getFieldIdByName($fields, "picture".$i)] = $images[$i];
                }

                //On peut peut-être fusionner les deux for suivants ? 

                foreach($fields as $field) {
                    if(!array_key_exists($field["id"], $arrayAd)) {
                        $arrayAd[$field["id"]] = $metas["ad".ucfirst($field["name"])];
                    }
                }

                for($i=1; $i <= intval($optionsExports["maxCSVColumn"]); $i++) { //Ou au moins optimiser ça ?
                    if(!array_key_exists($i, $arrayAd)) {
                        $arrayAd[$i] = "";
                    }
                }
                ksort($arrayAd);

                array_push($arrayAds, $arrayAd);
            }
        }
        return $arrayAds;
    }
    
    private static function createCSV($ads) {        
        $optionsExports = get_option(PLUGIN_RE_NAME."OptionsExports");
        $dirSaves = ABSPATH.$optionsExports["dirExportPath"];
        
        if(!is_dir($dirSaves)) {
            mkdir($dirSaves);
        }

        $CSVFile = fopen($dirSaves."Annonces.csv", "w+");
        
        $CSVContent = "\xEF\xBB\xBF";
        foreach($ads as $ad) {
            foreach($ad as $value) { //Quelle idée d'utiliser 2 caractères comme délimiteur(s)
                $CSVContent .= chr(34).strval($value).chr(34)."!#";
            }
            $CSVContent .= PHP_EOL;            
        }

        fwrite($CSVFile, $CSVContent);
        fclose($CSVFile);
    }
    
    private static function createConfigFile() {
        $optionsExports = get_option(PLUGIN_RE_NAME."OptionsExports");
        $dirExport = ABSPATH.$optionsExports["dirExportPath"];
        $content = "Version=".$optionsExports["versionSeLoger"]
                . "\nApplication=".PLUGIN_RE_NAME.'/'.PLUGIN_RE_VERSION
                . "\nDevise=Euro";
        $configFile = fopen($dirExport."Config.txt", "a");
        fwrite($configFile, $content);
        fclose($configFile);
    }
    
    private static function createPhotosFile() {
        $optionsExports = get_option(PLUGIN_RE_NAME."OptionsExports");
        $dirExport = ABSPATH.$optionsExports["dirExportPath"];
        $content = "Mode=URL";
        $photosFile = fopen($dirExport."Photos.cfg", "a");
        fwrite($photosFile, $content);
        fclose($photosFile);
    }
    
    private static function createZIP() {
        $optionsExports = get_option(PLUGIN_RE_NAME."OptionsExports");
        $dirExport = ABSPATH.$optionsExports["dirExportPath"];
        $zip = new ZipArchive();
        $zipName = PLUGIN_RE_NAME.'_'.$optionsExports["idAgency"].".zip";
        if(file_exists($dirExport."Annonces.csv") && file_exists($dirExport."Config.txt") && file_exists($dirExport."Photos.cfg")) {
            if($zip->open($dirExport.$zipName, ZipArchive::CREATE) === true) {
                if($zip->addFile($dirExport."Annonces.csv", "Annonces.csv") && 
                        $zip->addFile($dirExport."Config.txt", "Config.txt") && 
                        $zip->addFile($dirExport."Photos.cfg", "Photos.cfg")) {
                    $zip->close();
                    unlink($dirExport."Annonces.csv");
                    unlink($dirExport."Config.txt");
                    unlink($dirExport."Photos.cfg");
                }
            }
        }
    }
    
    private static function startExport() {
        SELF::createCSV(SELF::getPreparedAds());
        SELF::createConfigFile();
        SELF::createPhotosFile();
        SELF::createZIP();
    }
    
    private static function getFieldIdByName($fields, $name) {
        foreach($fields as $field) {
            if($field["name"] === $name) {
                return $field["id"];
            }
        }
    }
    
    
    
}
