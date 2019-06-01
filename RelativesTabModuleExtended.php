<?php

namespace Cissee\Webtrees\Module\Relatives;

use Cissee\Webtrees\Hook\HookInterfaces\RelativesTabExtenderInterface;
use Cissee\Webtrees\Hook\HookInterfaces\RelativesTabExtenderUtils;
use Cissee\WebtreesExt\Module\RelativesTabModule_2x;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Http\Controllers\Admin\ModuleController;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleTabInterface;
use Fisharebest\Webtrees\Services\ModuleService;
use ReflectionObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Vesta\Model\GenericViewElement;
use Vesta\VestaAdminController;
use Vesta\VestaModuleTrait;
use function route;
use function view;

class RelativesTabModuleExtended extends RelativesTabModule_2x implements ModuleCustomInterface, ModuleConfigInterface, ModuleTabInterface {

  use VestaModuleTrait;
  use RelativesTabModuleTrait;

  protected $module_service;

  public function __construct(ModuleService $module_service) {
    $this->module_service = $module_service;
  }
  
  protected function onBoot(): void {
    //we do not want to use the original name 'modules/relatives/tab' here, so we use our own namespace
    $this->setViewName($this->name() . '::tab');
  }
  
  public function customModuleAuthorName(): string {
    return 'Richard Cissée';
  }

  public function customModuleVersion(): string {
    return '2.0.0-beta.2.2';
  }

  public function customModuleLatestVersionUrl(): string {
    return 'https://cissee.de';
  }

  public function customModuleSupportUrl(): string {
    return 'https://cissee.de';
  }

  public function description(): string {
    return $this->getShortDescription();
  }

  /**
   * Where does this module store its resources
   *
   * @return string
   */
  public function resourcesFolder(): string {
    return __DIR__ . '/resources/';
  }

  public function tabTitle(): string {
    return $this->getTabTitle(I18N::translate('Families'));
  }

  protected function getOutputBeforeTab(Individual $person) {
    $pre = ''; //<link href="' . Webtrees::MODULES_PATH . basename($this->directory) . '/style.css" type="text/css" rel="stylesheet" />';

    $a1 = array(new GenericViewElement($pre, ''));
    $a2 = RelativesTabExtenderUtils::accessibleModules($this, $person->tree(), Auth::user())
            ->map(function (RelativesTabExtenderInterface $module) use ($person) {
              return $module->hRelativesTabGetOutputBeforeTab($person);
            })
            ->toArray();

    return GenericViewElement::implode(array_merge($a1, $a2));
  }

  protected function getOutputAfterTab(Individual $person) {
    $a = RelativesTabExtenderUtils::accessibleModules($this, $person->tree(), Auth::user())
            ->map(function (RelativesTabExtenderInterface $module) use ($person) {
              return $module->hRelativesTabGetOutputAfterTab($person);
            })
            ->toArray();
    return GenericViewElement::implode($a);
  }

  protected function getOutputInDescriptionBox(Individual $person) {
    return GenericViewElement::implode(RelativesTabExtenderUtils::accessibleModules($this, $person->tree(), Auth::user())
                            ->map(function (RelativesTabExtenderInterface $module) use ($person) {
                              return $module->hRelativesTabGetOutputInDBox($person);
                            })
                            ->toArray());
  }

  protected function getOutputAfterDescriptionBox(Individual $person) {
    return GenericViewElement::implode(RelativesTabExtenderUtils::accessibleModules($this, $person->tree(), Auth::user())
                            ->map(function (RelativesTabExtenderInterface $module) use ($person) {
                              return $module->hRelativesTabGetOutputAfterDBox($person);
                            })
                            ->toArray());
  }

  protected function getOutputFamilyAfterSubHeaders(Family $family, $type) {
    return GenericViewElement::implode(RelativesTabExtenderUtils::accessibleModules($this, $family->tree(), Auth::user())
                            ->map(function (RelativesTabExtenderInterface $module) use ($family, $type) {
                              return $module->hRelativesTabGetOutputFamAfterSH($family, $type);
                            }));
  }

  protected function printFamilyChild(Family $family, Individual $child) {
    foreach ($child->facts(['FAMC'], false, Auth::PRIV_HIDE, true) as $fact) {
      //$family = $fact->target();
      $xref = trim($fact->value(), '@');

      if ($xref === $family->xref()) {
        //check linkage status
        $stat = $fact->attribute("STAT");
        if ('challenged' === $stat) {
          $text = I18N::translate('linkage challenged');
          $title = I18N::translate('Linking this child to this family is suspect, but the linkage has been neither proven nor disproven.');
          // Show warning triangle + text
          echo '<div class="linkage small" title="' . $title . '">' . view('icons/warning') . $text . '</div>';
        } else if ('disproven' === $stat) {
          $text = I18N::translate('linkage disproven');
          $title = I18N::translate('There has been a claim by some that this child belongs to this family, but the linkage has been disproven.');
          // Show warning triangle + text
          echo '<div class="linkage small" title="' . $title . '">' . view('icons/warning') . $text . '</div>';
        }
      }
    }
  }

  //////////////////////////////////////////////////////////////////////////////
  //hook management - generalize?
  //adapted from ModuleController (e.g. listFooters)
  public function getProvidersAction(): ResponseInterface {
    $modules = RelativesTabExtenderUtils::modules($this, true);

    $controller = new VestaAdminController($this->name());
    return $controller->listHooks(
                    $modules,
                    RelativesTabExtenderInterface::class,
                    I18N::translate('Relatives Tab UI Element Providers'),
                    '',
                    true,
                    true);
  }

  public function postProvidersAction(ServerRequestInterface $request): ResponseInterface {
    $modules = RelativesTabExtenderUtils::modules($this, true);

    $controller1 = new ModuleController($this->module_service);
    $reflector = new ReflectionObject($controller1);

    //private!
    //$controller1->updateStatus($modules, $request);

    $method = $reflector->getMethod('updateStatus');
    $method->setAccessible(true);
    $method->invoke($controller1, $modules, $request);

    RelativesTabExtenderUtils::updateOrder($this, $request);

    //private!
    //$controller1->updateAccessLevel($modules, RelativesTabExtenderInterface::class, $request);

    $method = $reflector->getMethod('updateAccessLevel');
    $method->setAccessible(true);
    $method->invoke($controller1, $modules, RelativesTabExtenderInterface::class, $request);

    $url = route('module', [
        'module' => $this->name(),
        'action' => 'Providers'
    ]);

    return redirect($url);
  }

  protected function editConfigBeforeFaq() {
    $modules = RelativesTabExtenderUtils::modules($this, true);

    $url = route('module', [
        'module' => $this->name(),
        'action' => 'Providers'
    ]);

    //cf control-panel.phtml
    ?>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-6">
                <ul class="fa-ul">
                    <li>
                        <span class="fa-li"><?= view('icons/block') ?></span>
                        <a href="<?= e($url) ?>">
                            <?= I18N::translate('Relatives Tab UI Element Providers') ?>
                        </a>
                        <?= view('components/badge', ['count' => $modules->count()]) ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>		

    <?php
  }

}
