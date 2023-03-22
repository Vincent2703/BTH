<?php

class Export {    
    
    public function showPage() {     
        $postType = get_current_screen()->post_type;
        $base = get_current_screen()->base;
        $files = array_diff(scandir(PLUGIN_RE_PATH."exports"), array('.', ".."));
        usort($files, function($a, $b) {
            return filemtime(PLUGIN_RE_PATH."exports/$b") - filemtime(PLUGIN_RE_PATH."exports/$a");
        });
        ?>
        <div class="wrap">
            <h2>Exportez les annonces</h2>
            <form action="" method="post">
                <?php wp_nonce_field("formExportAds", "nonceSecurity"); ?>
                <p>
                    <input type="submit" name="submitExport" class="button button-primary" value="Exporter">                
                    <input type="checkbox" id="onlyAvailable" name="onlyAvailable" checked>
                    <label for="onlyAvailable">Exporter uniquement les biens disponibles</label>
                </p>
            </form>
            <?php if($postType==="re-ad" && $base="repexport") { ?>
            <table>
                <thead>
                    <tr>
                        <th><?php _e("Date", "retxtdom"); ?></th>
                        <th><?php _e("Size", "retxtdom"); ?></th>
                        <th><?php _e("Download", "retxtdom"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($files as $file) { ?>
                    <tr>
                        <td><?= date("Y-m-d h:i:s", filemtime(PLUGIN_RE_PATH."exports/$file")); ?></td>
                        <td><?= round(filesize(PLUGIN_RE_PATH."exports/$file")/1024, 2); ?>&nbsp;kb</td>
                        <td><a href="<?=plugin_dir_url(__DIR__)."exports/$file";?>" download><?php _e("Download", "retxtdom"); ?></a></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php } ?>
        </div>
        <?php
        if(isset($_POST["submitExport"]) && isset($_POST["nonceSecurity"]) && wp_verify_nonce($_POST["nonceSecurity"], "formExportAds")) {
            SELF::startExport();
            echo "Exportation effectuée avec succès";
        }
    }
    
    public function widgetExport() {
        wp_add_dashboard_widget(PLUGIN_RE_NAME."widgetExport", "Exporter les annonces", array($this, "showPage"));
    }

    private static function getArrayAds() {
        $optionsFees = get_option(PLUGIN_RE_NAME."OptionsFees");
        if(isset($optionsFees["feesUrl"])) {
            $feesUrl = $optionsFees["feesUrl"];
        }else{
            $feesUrl = '';
        }

        $args = array(
            "numberposts" => -1,
            "post_type" => "re-ad",
            "post_status" => "publish"
        );
        if(isset($_POST["onlyAvailable"])) { //Si on veut seulement les annonces qui sont dispos à la location/vente
            $args["tax_query"] = array(
                array(
                    "taxonomy" => "adAvailable",
                    "field" => "slug",
                    "terms" => array("available")
                )
            );
        }

        $ads = get_posts($args);
               
        $arrayAds = array();                

        if(!empty($ads)) {
            foreach($ads as $ad) {
                $adID = $ad->ID;
                $metas = array_map(function($n) {return $n[0];}, get_post_meta($adID));         
                unset($metas["_thumbnail_id"]);
                unset($metas["_edit_lock"]);
                unset($metas["_edit_last"]);              

                //Récupérer infos agent
                $agentID = get_post($metas["adIdAgent"])->ID;
                $agentData = array(
                    "name"          =>  html_entity_decode(get_the_title($agentID, ENT_COMPAT, "UTF-8")),
                    "phone"         =>  get_post_meta($agentID, "agentPhone", true),
                    "mobilePhone"   =>  get_post_meta($agentID, "agentMobilePhone", true),
                    "email"         =>  get_post_meta($agentID, "agentEmail", true)
                );
                
                $agencyID = wp_get_post_parent_id($agentID);
                $agencyData = array(
                    "name"      =>  html_entity_decode(get_the_title($agencyID, ENT_COMPAT, "UTF-8")),
                    "phone"     =>  get_post_meta($agencyID, "agencyPhone", true),
                    "email"     =>  get_post_meta($agencyID, "agencyEmail", true),
                    "feesUrl"   =>  sanitize_url($optionsFees["feesUrl"])
                );
                

                $adData = array(
                    "title"         =>  html_entity_decode(get_the_title($adID, ENT_COMPAT, "UTF-8")),
                    "typeAd"        =>  get_the_terms($adID, "adTypeAd")[0]->name,
                    "typeProperty"  =>  get_the_terms($adID, "adTypeProperty")[0]->name,
                    "description"   =>  html_entity_decode(get_the_content(null, null, $adID), ENT_COMPAT, "UTF-8"), 
                    "thumbnail"     =>  get_the_post_thumbnail_url($adID, "large")
                );
                
                $uselessKeys = array("adDataMap", "adImages");
                
                foreach($metas as $metaKey=>$metaValue) {
                    if(!in_array($metaKey, $uselessKeys)) {
                        $metaKey = strtolower(str_replace("ad", '', $metaKey));
                        $adData[$metaKey] = $metaValue;
                    }
                }
                
                $picturesIds = $metas["adImages"]; //On récupère les images
                $pictures = array();
                if(!is_null($picturesIds)) {
                    $ids = explode(';', $picturesIds); //Les IDs sont séparés par un ;
                    foreach ($ids as $id) {
                        $pictures["img$id"] = wp_get_attachment_image_url($id, "large"); //Pour chaque image on récupère leur URL
                    }
                }
                $adData["pictures"] = $pictures;
                
                $allData = array(           
                    "adData"        => $adData,
                    "agentData"     => $agentData,
                    "agencyData"    => $agencyData,
                );             

                $arrayAds["ad$adID"] = $allData;
            }
        }
        return $arrayAds;
        
    }
    
    private static function generateXML($data, &$xml) {             
        foreach($data as $key => $value ) {
            if(is_array($value)) {
                if(is_numeric($key)){
                    $key = "key$key"; 
                }
                $subNode = $xml->addChild($key);
                SELF::generateXML($value, $subNode);
            }else{
                $xml->addChild("$key", "$value");
            }
        }
        $xmlString = $xml->asXML();
        $xmlDocument = new DOMDocument("1.0", "UTF-8");
        $xmlDocument->preserveWhiteSpace = false;
        $xmlDocument->formatOutput = true;
        $xmlDocument->loadXML($xmlString);
        $xmlDocument->encoding = "utf-8";
        return $xmlDocument->saveXML();
    }
   
    
    /*
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
    }*/
    
    private static function startExport() {
        $dirExport = PLUGIN_RE_PATH."exports";
        
        if(!is_dir($dirExport)) {
            mkdir($dirExport);
        }
        
        $ads = SELF::getArrayAds();

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><ads></ads>');
        $XMLContent = "\xEF\xBB\xBF".SELF::generateXML($ads, $xml).PHP_EOL;
        
        $path = $dirExport.'/'. uniqid(__("ads_", "retxtdom")).".xml";
        $XMLFile = fopen($path, "w+");
        
        fwrite($XMLFile, $XMLContent);
        fclose($XMLFile);        
        
        return $path;
        
        /*SELF::createCSV(SELF::getArrayAds());
        SELF::createConfigFile();
        SELF::createPhotosFile();
        SELF::createZIP();*/ 
    }
    
    private static function download($path) {
        //$filename = get_bloginfo("name").' '.__("Ads exports", "retxtdom").' '.date("Y-m-d");

        /*header("Content-type: application/xml");
        header('Content-Disposition: inline; filename="'.$filename.'"');
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($path));
        header("Accept-Ranges: bytes");*/
        @readfile($path);
    } 
    
    private static function deleteLastExport($path) {
        unset($path);
    }
}
