<?php

namespace Cissee\Webtrees\Hook\HookInterfaces;

use Cissee\WebtreesExt\FormatPlaceAdditions;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Individual;
use Vesta\Model\GenericViewElement;
use Vesta\Model\PlaceStructure;

/**
 * base impl of RelativesTabExtenderInterface
 */
trait EmptyRelativesTabExtender {

  protected $relativesTabUIElementOrder = 0;

  public function setRelativesTabUIElementOrder(int $order): void {
    $this->relativesTabUIElementOrder = $order;
  }

  public function getRelativesTabUIElementOrder(): int {
    return $this->relativesTabUIElementOrder ?? $this->defaultRelativesTabUIElementOrder();
  }

  public function defaultRelativesTabUIElementOrder(): int {
    return 9999;
  }

  public function hRelativesTabGetOutputBeforeTab(Individual $person) {
    return new GenericViewElement('', '');
  }

  public function hRelativesTabGetOutputAfterTab(Individual $person) {
    return new GenericViewElement('', '');
  }

  public function hRelativesTabGetOutputInDBox(Individual $person) {
    return new GenericViewElement('', '');
  }

  public function hRelativesTabGetOutputAfterDBox(Individual $person) {
    return new GenericViewElement('', '');
  }

  public function hRelativesTabGetOutputFamAfterSH(Family $family, $type) {
    return new GenericViewElement('', '');
  }

}
