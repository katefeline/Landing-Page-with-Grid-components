<?php

namespace Drupal\ims_landing_page_with_grid\Service;

use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Handles preprocess logic for IMS paragraph types.
 */
class ImsParagraphPreprocessor {

  public function __construct(
    protected FileUrlGeneratorInterface $fileUrlGenerator,
  ) {}

  /**
   * Preprocess variables for the ims_social_media_item paragraph.
   */
  public function preprocessSocialMediaItem(array &$variables): void {
    /** @var Paragraph $paragraph */
    $paragraph = $variables['paragraph'];

    $image = $this->extractImageData($paragraph, 'field_ims_social_media_item_img');
    $variables['social_media_image_url'] = $image['url'];
    $variables['social_media_image_alt'] = $image['alt'];

    $link = $this->extractLinkData($paragraph, 'field_ims_social_media_item_link');
    $variables['social_media_link_url'] = $link['url'];
    $variables['social_media_link_target'] = $link['target'];
    $variables['social_media_link_rel'] = $link['rel'];
  }

  /**
   * Preprocess variables for the ims_grid_item paragraph.
   */
  public function preprocessGridItem(array &$variables): void {
    /** @var Paragraph $paragraph */
    $paragraph = $variables['paragraph'];

    $variables['grid_title'] = '';
    $variables['grid_subtitle'] = '';

    if ($paragraph->hasField('field_ims_grid_item_title') && !$paragraph->get('field_ims_grid_item_title')->isEmpty()) {
      $variables['grid_title'] = $paragraph->get('field_ims_grid_item_title')->value;
    }

    if ($paragraph->hasField('field_ims_grid_item_subtitle') && !$paragraph->get('field_ims_grid_item_subtitle')->isEmpty()) {
      $variables['grid_subtitle'] = $paragraph->get('field_ims_grid_item_subtitle')->value;
    }

    $image = $this->extractImageData($paragraph, 'field_ims_grid_item_img');
    $variables['grid_image_url'] = $image['url'];
    $variables['grid_image_alt'] = $image['alt'];

    $link = $this->extractLinkData($paragraph, 'field_ims_grid_item_link');
    $variables['grid_link_url'] = $link['url'];
    $variables['grid_link_target'] = $link['target'];
    $variables['grid_link_rel'] = $link['rel'];
    $variables['grid_link_text'] = $link['text'];
  }

  /**
   * Extracts image URL and alt text from a media entity reference field.
   *
   * @return array{url: string, alt: string}
   */
  private function extractImageData(Paragraph $paragraph, string $fieldName): array {
    if (!$paragraph->hasField($fieldName) || $paragraph->get($fieldName)->isEmpty()) {
      return ['url' => '', 'alt' => ''];
    }

    $media = $paragraph->get($fieldName)->entity;
    $image_item = $media->get('field_media_image')->first();

    if ($image_item && $image_item->entity) {
      return [
        'url' => $this->fileUrlGenerator->generateString($image_item->entity->getFileUri()),
        'alt' => $image_item->alt ?? 'Default',
      ];
    }

    return ['url' => '', 'alt' => ''];
  }

  /**
   * Extracts URL, target, rel and link text from a link field.
   *
   * @return array{url: string, target: string, rel: string, text: string}
   */
  private function extractLinkData(Paragraph $paragraph, string $fieldName): array {
    $defaults = ['url' => '', 'target' => '', 'rel' => '', 'text' => ''];

    if (!$paragraph->hasField($fieldName) || $paragraph->get($fieldName)->isEmpty()) {
      return $defaults;
    }

    $link_item = $paragraph->get($fieldName)->first();
    $url = $link_item->getUrl();

    $target = '';
    $rel = '';

    if ($url) {
      $options = $url->getOptions();
      $target = $options['attributes']['target'] ?? '';
      $rel = $options['attributes']['rel'] ?? '';
      if (is_array($rel)) {
        $rel = implode(' ', $rel);
      }
    }

    return [
      'url' => $url ? $url->toString() : '',
      'target' => $target,
      'rel' => $rel,
      'text' => $link_item->title ?? '',
    ];
  }

}

