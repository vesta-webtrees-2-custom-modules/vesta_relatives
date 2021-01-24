<?php

namespace Cissee\Webtrees\Module\Relatives;

use Cissee\WebtreesExt\Http\RequestHandlers\RelativesTabExtenderProvidersAction;
use Cissee\WebtreesExt\Module\RelativesTabModule_2x;
use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleTabInterface;
use Fisharebest\Webtrees\Services\ModuleService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Vesta\Hook\HookInterfaces\RelativesTabExtenderInterface;
use Vesta\Hook\HookInterfaces\RelativesTabExtenderUtils;
use Vesta\Model\GenericViewElement;
use Vesta\VestaAdminController;
use Vesta\VestaModuleTrait;
use function route;
use function view;

class RelativesTabModuleExtended extends RelativesTabModule_2x implements 
  ModuleCustomInterface, 
  ModuleConfigInterface, 
  ModuleTabInterface {

  //must not use ModuleTabTrait here - already used in transitive superclass RelativesTabModule,
  //and - more importantly - partially implemented there! (supportedFacts)
  use ModuleCustomTrait, ModuleConfigTrait, VestaModuleTrait {
    VestaModuleTrait::customTranslations insteadof ModuleCustomTrait;
    VestaModuleTrait::customModuleLatestVersion insteadof ModuleCustomTrait;
    VestaModuleTrait::getAssetAction insteadof ModuleCustomTrait;
    VestaModuleTrait::assetUrl insteadof ModuleCustomTrait;
    
    VestaModuleTrait::getConfigLink insteadof ModuleConfigTrait;
  }
  
  use RelativesTabModuleTrait;

  protected $module_service;

  public function __construct(ModuleService $module_service) {
    $this->module_service = $module_service;
  }

  //assumes to get called after setName!
  protected function getViewName(): string {
    //we do not want to use the original name 'modules/relatives/tab' here, so we use our own namespace
    return $this->name() . '::tab';
  }
  
  public function customModuleAuthorName(): string {
    return 'Richard Cissée';
  }

  public function customModuleVersion(): string {
    return file_get_contents(__DIR__ . '/latest-version.txt');
  }

  public function customModuleLatestVersionUrl(): string {
    return 'https://raw.githubusercontent.com/vesta-webtrees-2-custom-modules/vesta_relatives/master/latest-version.txt';
  }

  public function customModuleSupportUrl(): string {
    return 'https://cissee.de';
  }
  
  public function resourcesFolder(): string {
    return __DIR__ . '/resources/';
  }

  public function tabTitle(): string {
    return $this->getTabTitle(MoreI18N::xlate('Families'));
  }

  //there may be further ajax calls from this tab so we suggest to load tab itself via ajax
  public function canLoadAjax(): bool {
    return true;
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
  
  private function title1(): string {
    return /* I18N: Module Configuration */I18N::translate('Families Tab UI Element Providers');
  }
  
  private function description1(): string {
    return /* I18N: Module Configuration */I18N::translate('Modules listed here may provide additional data for families (displayed in the configured order).');
  }
  
  //hook management - generalize?
  //adapted from ModuleController (e.g. listFooters)
  public function getRelativesTabExtenderProvidersAction(): ResponseInterface {
    $modules = RelativesTabExtenderUtils::modules($this, true);

    $controller = new VestaAdminController($this->name());
    return $controller->listHooks(
                    $modules,
                    RelativesTabExtenderInterface::class,
                    $this->title1(),
                    $this->description1(),
                    true,
                    true);
  }

  public function postRelativesTabExtenderProvidersAction(ServerRequestInterface $request): ResponseInterface {
    $controller = new RelativesTabExtenderProvidersAction($this);
    return $controller->handle($request);
  }

  protected function editConfigBeforeFaq() {
    $modules = RelativesTabExtenderUtils::modules($this, true);

    $url = route('module', [
        'module' => $this->name(),
        'action' => 'RelativesTabExtenderProviders'
    ]);

    //cf control-panel.phtml
    ?>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-9">
                <ul class="fa-ul">
                    <li>
                        <span class="fa-li"><?= view('icons/block') ?></span>
                        <a href="<?= e($url) ?>">
                            <?= $this->title1() ?>
                        </a>
                        <?= view('components/badge', ['count' => $modules->count()]) ?>
                        <p class="small text-muted">
                          <?= $this->description1() ?>
                        </p>
                    </li>
                </ul>
            </div>
        </div>
    </div>		

    <?php
  }

}
