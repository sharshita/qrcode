<?php

namespace Drupal\qr_code\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;

/**
 * Provides a block with a QR code.
 *
 * @Block(
 *   id = "qrcode_block",
 *   admin_label = @Translation("QR code block"),
 * )
 */
class QrcodeBlock extends BlockBase implements ContainerFactoryPluginInterface  {
  
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Constructs a new QR code block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   The current route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, CurrentRouteMatch $currentRouteMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->currentRouteMatch = $currentRouteMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
      $container->get('current_route_match')
    );
  }
  
  /**
   * {@inheritdoc}
   */

  public function build() {
    $nid = $this->currentRouteMatch->getRawParameter('node');
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
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
      ],
      '#cache' => [
        'tags' => ['node:'.$nid],
      ]
    ];
  
  }

}
