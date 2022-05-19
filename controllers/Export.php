<?php
//Penser à revoir les fonctions publiques et privées
class Export {    
    const VERSION = 1;
    const MAXCSVCOLUMN = 328;
    private $optionsExport;
    
    private function getFieldIdByName($fields, $name) {
        foreach($fields as $field) {
            if($field["name"] === $name) {
                return $field["id"];
            }
        }
    }
    
    public function showPage() {
        $this->optionsExport = get_option(PLUGIN_RE_NAME."OptionsExports");
        $this->optionsFees = get_option(PLUGIN_RE_NAME."OptionsFees");
        
        $postType = get_current_screen()->post_type;
        $base = get_current_screen()->base;
        ?>
        <div class="wrap">
            <?php if($postType==="ad" && $base="bthexport") { ?>
                <h2>Exportez les annonces</h2>
            <?php } ?>
            <form action="" method="post">
                <label for="publishing">Publier sur les sites partenaires : </label><input type="checkbox" id="publishing" name="publishing">
                <input type="submit" class="button button-primary" name="submitExport" value="Exporter">
                <a href="?downloadSave" class="button button-primary">Télécharger une sauvegarde</a>
            </form>
        </div>
        <?php
        if(isset($_POST["submitExport"])) {
            $this->startExport();
        }
    }
    
    public function widgetExport() {
        wp_add_dashboard_widget(PLUGIN_RE_NAME."widgetExport", "Exporter les annonces", array($this, "showPage"));
    }

    private function getPreparedAds() {
        $this->optionsExport = get_option(PLUGIN_RE_NAME."OptionsExports");
        $fields = get_option(PLUGIN_RE_NAME."OptionsMapping");

        $args = array(
            "numberposts" => -1,
            "post_type" => "ad",
            "post_status" => "publish"
        );
        $ads = get_posts($args);
        $arrayAds = array();
                
        foreach($ads as $ad) {
            $metas = array_map(function($n) {return $n[0];}, get_post_meta($ad->ID));
            if($metas["adAvailable"] === "on") { //On vérifie que le bien est toujours dispo
                
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
                if($metas["adShowAgent"] === "on") {
                    if($agent = get_post($metas["adAgent"])) {
                        $agentEmail = get_post_meta($agent, "agentEmail", true);
                    }
                }else{
                    $agentEmail = "";
                }
                
                $coordsGPS = explode(',', $metas["adDataMap"], -2); //On récupère les coordonnées GPS
                //print_r($coordsGPS);

                $arrayAd = [
                    1                                                => $this->optionsExport["idAgency"],
                    $this->getFieldIdByName($fields, "title")        => get_the_title($ad),
                    $this->getFieldIdByName($fields, "typeAd")       => get_the_terms($ad, "adTypeAd")[0]->name,
                    $this->getFieldIdByName($fields, "typeProperty") => get_the_terms($ad, "adTypeProperty")[0]->name,
                    $this->getFieldIdByName($fields, "description")  => get_the_content($ad),
                    //$this->getFieldIdByName($fields, "latitude")     => $coordsGPS[0],
                    //$this->getFieldIdByName($fields, "longitude")    => $coordsGPS[1],
                    $this->getFieldIdByName($fields, "agentEmail")   => $agentEmail,
                    $this->getFieldIdByName($fields, "feesAgency")   => $this->optionsFees["feesUrl"]
                ];
                                
                $arrayAd[$this->getFieldIdByName($fields, "thumbnail")] = $images[0];
                for($i=1; $i<=8; $i++) {
                    $arrayAd[$this->getFieldIdByName($fields, "picture".$i)] = $images[$i];
                }
                
                //On peut peut-être fusionner les deux for suivants ? 
                
                foreach($fields as $field) {
                    if(!array_key_exists($field["id"], $arrayAd)) {
                        $arrayAd[$field["id"]] = $metas["ad".ucfirst($field["name"])];
                    }
                }
                
                for($i=1; $i <= self::MAXCSVCOLUMN; $i++) { //Ou au moins optimiser ça ?
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
    
    private function createCSV($ads) {        
        $dirSaves = ABSPATH.$this->optionsExport["dirExportPath"];
        
        if(!is_dir($dirSaves)) {
            mkdir($dirSaves);
        }

        $CSVFile = fopen($dirSaves."Annonces.csv", "w+");
        
        $CSVContent = "";
        foreach($ads as $ad) {
            foreach($ad as $value) { //Quelle idée d'utiliser 2 caractères comme délimiteur(s)
                $CSVContent .= chr(34).strval($value).chr(34)."!#";
            }
            $CSVContent .= PHP_EOL;
            //fputcsv($CSVFile, $ad, ';');
            
        }
        //rewind($CSVFile);
        //$CSVContent = str_replace('";"', '"!#"', stream_get_contents($CSVFile));
        utf8_encode($CSVContent);
        fwrite($CSVFile, $CSVContent);
        fclose($CSVFile);
    }
    
    private function createConfigFile() {
        $dirExport = ABSPATH.$this->optionsExport["dirExportPath"];
        $content = "Version=".SELF::VERSION
                . "\nApplication=".PLUGIN_RE_NAME.'/'.PLUGIN_RE_VERSION
                . "\nDevise=Euro";
        $configFile = fopen($dirExport."Config.txt", "a");
        fwrite($configFile, $content);
        fclose($configFile);
    }
    
    private function createPhotosFile() {
        $dirExport = ABSPATH.$this->optionsExport["dirExportPath"];
        $content = 'Mode=URL';
        $photosFile = fopen($dirExport."Photos.cfg", "a");
        fwrite($photosFile, $content);
        fclose($photosFile);
    }
    
    private function createZIP() {
        $dirExport = ABSPATH.$this->optionsExport["dirExportPath"];
        $zip = new ZipArchive();
        $zipName = PLUGIN_RE_NAME.'_'.$this->optionsExport["idAgency"].".zip";
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
    
    private function startExport() {
        $this->createCSV($this->getPreparedAds());
        $this->createConfigFile();
        $this->createPhotosFile();
        $this->createZIP();
    }
    
    
}
