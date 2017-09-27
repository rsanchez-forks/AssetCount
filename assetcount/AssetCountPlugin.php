<?php
namespace Craft;

class AssetCountPlugin extends BasePlugin
{
    public function getName()
    {
        return Craft::t('Asset Count');
    }

    public function getVersion()
    {
        return '0.0.1';
    }

    public function getDeveloper()
    {
        return 'S. Group';
    }

    public function getDeveloperUrl()
    {
        return 'http://sgroup.com.au';
    }

    protected function defineSettings()
    {
        return array(
            'showCountOnAssetIndex' => array(AttributeType::Bool, 'default' => 0),
            'ignoreLoggedInUsers' => array(AttributeType::Bool, 'default' => 0),
            'ignoreIpAddresses' => array(AttributeType::Mixed, 'default' => ''),
            'countLabel' => array(AttributeType::String, 'default' => 'Count'),
            'resetLabel' => array(AttributeType::String, 'default' => 'Reset Asset Count'),
        );
    }

    public function getSettingsHtml()
    {
       return craft()->templates->render('assetcount/settings', array(
           'settings' => $this->getSettings()
       ));
    }

    public function hasCpSection()
    {
        return true;
    }

    public function init()
    {
        parent::init();

        if (!craft()->isConsole() && craft()->request->isCpRequest()) {
            craft()->on('elements.onBeforeBuildElementsQuery', function ($event) {
                $query = $event->params['query'];

                $criteria = $event->params['criteria'];

                $elementType = $criteria->getElementType();

                if ($elementType instanceof AssetElementType) {
                    $query->select('assetcount.count');

                    $query->leftJoin('assetcount assetcount', 'assetcount.assetId = elements.id');
                }
            });
        }
    }

    public function defineAdditionalAssetTableAttributes()
    {
        $attributes = array();

        if ($this->getSettings()->showCountOnAssetIndex)
        {
            $attributes['count'] = Craft::t($this->getSettings()->countLabel);
        }

        return $attributes;
    }

	// Hooks
	// =========================================================================

    public function modifyAssetSortableAttributes(&$attributes)
    {
        if ($this->getSettings()->showCountOnAssetIndex)
        {
            $attributes['count'] = Craft::t($this->getSettings()->countLabel);
        }
    }

    public function getAssetTableAttributeHtml($asset, $attribute)
    {
        if ($this->getSettings()->showCountOnAssetIndex AND $attribute == 'count')
        {
            return craft()->assetCount->getCount($asset->id)->count;
        }
    }

    public function addAssetActions($source)
    {
        if ($this->getSettings()->showCountOnAssetIndex)
        {
            Craft::import('plugins.assetcount.elementactions.AssetCount_ResetAction');

            $action = new AssetCount_ResetAction();

            $action->setName($this->getSettings()->resetLabel);

            return array($action);
        }
    }
}
