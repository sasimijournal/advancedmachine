<?php
/**
 * @package      Prism
 * @subpackage   Integrations\Profiles
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Prism\Integration\Profiles;

use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;
use Prism\Filesystem\Helper;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods which creates social profiles object,
 * based on social extension name.
 *
 * @package      Prism
 * @subpackage   Integrations\Profiles
 *
 * @deprecated v1.20
 */
class Builder
{
    protected $config = array();
    protected $profiles;

    /**
     * Initialize the object.
     *
     * <code>
     * $options = array(
     *    "social_platform" => "socialcommunity",
     *    "users_ids" => array(1,2,3)
     * );
     *
     * $profilesBuilder = new Prism\Integration\Profiles\Builder($options);
     * </code>
     *
     * @param  array  $config Options used in the process of building profile object.
     *
     */
    public function __construct(array $config = array())
    {
        $this->config = $config;
    }

    /**
     * Build a social profile object.
     *
     * <code>
     * $options = array(
     *    "social_platform" => "socialcommunity",
     *    "users_ids" => array(1,2,3)
     * );
     *
     * $profilesBuilder = new Prism\Integration\Profiles\Builder($options);
     * $profilesBuilder->build();
     *
     * $profiles = $profilesBuilder->getProfiles();
     * </code>
     */
    public function build()
    {
        $type       = ArrayHelper::getValue($this->config, 'social_platform');
        $usersIds   = ArrayHelper::getValue($this->config, 'users_ids');

        switch ($type) {

            case 'socialcommunity':

                jimport('Socialcommunity.init');

                /** @var  $params Registry */
                $params = \JComponentHelper::getParams('com_socialcommunity');
                $filesystemHelper = new Helper($params);

                $url   = $filesystemHelper->getMediaFolderUri();

                $profiles = new Socialcommunity(\JFactory::getDbo());
                $profiles->load(array('user_ids' => $usersIds));
                $profiles->setMediaUrl($url);

                break;

            case 'gravatar':

                $profiles = new Gravatar(\JFactory::getDbo());
                $profiles->load($usersIds);

                break;

            case 'kunena':

                $profiles = new Kunena(\JFactory::getDbo());
                $profiles->load($usersIds);

                break;

            case 'jomsocial':

                // Register JomSocial Router
                if (!class_exists('CRoute')) {
                    \JLoader::register('CRoute', JPATH_SITE.'/components/com_community/libraries/core.php');
                }

                $profiles = new JomSocial(\JFactory::getDbo());
                $profiles->load($usersIds);

                // Load language file.
                $lang = \JFactory::getLanguage();
                $lang->load('com_community.country', JPATH_BASE);

                break;

            case 'easysocial':

                $profiles = new EasySocial(\JFactory::getDbo());
                $profiles->load($usersIds);

                break;

            case 'easyprofile':

                $profiles = new EasyProfile(\JFactory::getDbo());
                $profiles->load($usersIds);

                break;

            case 'communitybuilder':
                $profiles = new CommunityBuilder(\JFactory::getDbo());
                $profiles->load($usersIds);
                break;

            default:
                $profiles = null;
                break;
        }

        $this->profiles = $profiles;
    }

    /**
     * Return a social profiles object.
     *
     * <code>
     * $options = array(
     *    "social_platform" => "socialcommunity",
     *    "users_ids" => array(1,2,3)
     * );
     *
     * $profilesBuilder = new Prism\Integration\Profiles\Builder($options);
     * $profilesBuilder->build();
     *
     * $profiles = $profilesBuilder->getProfiles();
     * </code>
     *
     * @return null|ProfilesInterface
     */
    public function getProfiles()
    {
        return $this->profiles;
    }
}
