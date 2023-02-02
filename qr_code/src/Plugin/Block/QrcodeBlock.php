<?php

namespace Drupal\qr_code\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block with a QR code.
 *
 * @Block(
 *   id = "qrcode_block",
 *   admin_label = @Translation("QR code block"),
 * )
 */
class QrcodeBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */

  public function build() {
    $nid = \Drupal::routeMatch()->getRawParameter('node');
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    $url = $node->get('field_app_purchase_link')->getString(); 
    $path = '';
    $directory = "public://Images/QrCodes/";
    \Drupal::service('file_system')->prepareDirectory($directory, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);
    $qrName = 'myQrcode';
    $uri = $directory . 'QR'. '.png'; // Generates a png image.
    $path =  \Drupal::service('file_system')->realpath($uri);
    \PHPQRCode\QRcode::png($url, $path, 'L', 10, 6);
    $relative_file_url =  \Drupal::service('file_url_generator')
      ->generateAbsoluteString($uri); 
    $qr_image = "<img src='{$relative_file_url}'/>";
        
    return [
      '#markup' => $qr_image,
      '#attached' => [
        'library' => [
          'qr_code/qr_code'
        ]
      ]
      '#cache' => [
        'tags' => ['node:'$nid],
      ]
    ];
  
  }

}
