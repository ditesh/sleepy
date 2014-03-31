<?php

namespace Sleepy\Library;
use Sleepy\Exception;

class Configurator {

    private $container;

    public function __construct($container) {
        $container["configuration"] = [];
        $this->container = $container;
    }

    private function getFiles($path) {

        $filenames = [];

        if (is_dir($path)) {
            
            $filenames = @glob("$path/*");
            if ($filenames === FALSE) throw new Exception\ConfigurationException("Unable to glob $handlers/*");

        } else $filenames[] = $path;

        return $filenames;

    }

    private function parseFiles($filenames) {

        $retval = [];
        $filenames = $this->getFiles($path);

        foreach ($filenames as $filename) {

            try {

                $name = str_replace(".yml", "", basename($filename));
                $val = Yaml::parse(@file_get_contents($filename));
                $retval[$name] = $val;

            } catch (ParseException $e) {
                throw new Exception\ConfigurationException($e);
            }

        }

        return $retval;

    }

    public function configureOptions($path) {

        try {
            $this->container->configuration["options"] = $this->getFiles($path)->parseFiles();
        } catch (Exception\ConfigurationException $e) {
            throw $e;
        }

    }

    public function configureResources($path) {

        try {
            $this->container->configuration["resources"] = $this->getFiles($path)->parseFiles();
        } catch (Exception\ConfigurationException $e) {
            throw $e;
        }

    }

    public function configureHandlers($path) {

        $handlers = [];

        try {

            $filenames = $this->getFiles($path);
            $handlers = $this->parseFiles($filenames);

            foreach ($handlers as $name=>$options) {

                $handlers[$name] = function() use ($options) {
                    return new $name($options);
                };

            }

            $this->container->configuration["handlers"] = $handlers;

        } catch (Exception\ConfigurationException $e) {
            throw $e;
        }

    }
}
