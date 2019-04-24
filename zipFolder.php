<?php

namespace POSTGRESTOGWERN\PostgresToGwern;

class ZipFolder{
    public function __construct($dd){
        
        $rootPath = realpath($dd);        
        // Initialize archive object
        $zip = new \ZipArchive();
        $zip->open($rootPath.'.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        // Initialize empty "delete list"
        $filesToDelete = array();

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);

                // Add current file to "delete list"
                // delete it later cause ZipArchive create archive only after calling close function and ZipArchive lock files until archive created)
                    $filesToDelete[] = $filePath;                
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();

        // Delete all files from "delete list"
        foreach ($filesToDelete as $file)
        {
            unlink($file);
        }

        rmdir($rootPath);                
    
    }
}