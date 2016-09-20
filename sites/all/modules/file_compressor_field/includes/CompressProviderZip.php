<?php

/**
 * @file
 * Contains CompressProviderZip.
 */

/**
 * Implements Zip compressor for File Compressor field.
 */
class CompressProviderZip implements CompressProviderInterface {

  /**
   * The extension for files using this plugin.
   *
   * @var string
   */
  protected $extension = 'zip';

  /**
   * @{@inheritdoc}
   */
  public function getExtension() {
    return $this->extension;
  }

  /**
   * @{@inheritdoc}
   */
  public function generateCompressedFileUri($base_uri) {
    return "$base_uri.$this->extension";
  }

  /**
   * @{@inheritdoc}
   */
  public function generateCompressedFile($file_uri, $files) {
    $zip = new ZipArchive();
    if ($zip_open = $zip->open(drupal_realpath($file_uri), ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE)) {
      $zip->setArchiveComment('Generated by File Compressor field for Drupal.');
      // http://drupal.org/node/1234282 - duplicate filenames.
      foreach ($files as $file) {
        $path = drupal_realpath($file);
        if (file_exists($file)) {
          if (!$zip->addFile($path, basename($path))) {
            watchdog('file_compressor_field', 'Failed to add !file to Zip.', array('!file' => $file), WATCHDOG_ERROR);
          }
        }
        else {
          watchdog('file_compressor_field', 'Failed to locate !file.', array('!file' => $file), WATCHDOG_ERROR);
        }
      }
      if (!$zip->close()) {
        watchdog('file_compressor_field', 'Error saving file: !status_string', array('!status_string' => $zip->getStatusString()), WATCHDOG_ERROR);
        return FALSE;
      }
      return TRUE;
    }
    return FALSE;
  }
}