<?php

namespace Drupal\menu_section\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeTypeInterface;
use Drupal\system\MenuInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class MenuSectionNodeAddAccessCheck implements ContainerInjectionInterface {
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }


  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public function access(AccountInterface $account, NodeTypeInterface $node_type = NULL, MenuInterface $menu = NULL) {
    if ($node_type && $menu) {
      $uid = $menu->getThirdPartySetting('menu_section', 'uid');
      $allowedTypes = \Drupal::config('menu_section.settings')->get('allowed_types');
      if (in_array($node_type->id(), $allowedTypes) && isset($uid) && (int) $menu->getThirdPartySetting('menu_section', 'uid') === (int) $account->id()) {
        return AccessResult::allowed();
      }
    }
    return AccessResult::allowedIf($this->entityTypeManager->getAccessControlHandler('node')->createAccess($node_type->id(), $account));
  }

}
