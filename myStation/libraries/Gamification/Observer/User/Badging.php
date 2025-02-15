<?php
/**
 * @package      Gamification
 * @subpackage   Observers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Gamification\Observer\User;

use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;
use Gamification\User\Badge\BadgePointsSeeker;
use Gamification\Observer\PointsObserver;
use Gamification\User\Badge\BadgeManager;
use Gamification\User\Points\Points;
use Gamification\Helper;

defined('JPATH_PLATFORM') or die;

/**
 * Abstract class defining methods that can be
 * implemented by an Observer class of a JTable class (which is an Observable).
 * Attaches $this Observer to the $table in the constructor.
 * The classes extending this class should not be instantiated directly, as they
 * are automatically instantiated by the JObserverMapper
 *
 * @package      Gamification
 * @subpackage   Observers
 * @link         http://docs.joomla.org/JTableObserver
 * @since        3.1.2
 */
class Badging extends PointsObserver
{
    /**
     * Context that are allowed to be processed.
     *
     * @var array
     */
    protected $allowedContext = array('com_user.registration', 'com_content.read.article');

    /**
     * The pattern for this table's TypeAlias
     *
     * @var    string
     * @since  3.1.2
     */
    protected $typeAliasPattern;

    protected $sendNotification = false;
    protected $storeActivity    = false;

    /**
     * Creates the associated observer instance and attaches it to the $observableObject
     * $typeAlias can be of the form '{variableName}.type', automatically replacing {variableName} with table-instance variables variableName
     *
     * @param   \JObservableInterface $observableObject The subject object to be observed
     * @param   array                $params           ( 'typeAlias' => $typeAlias )
     *
     * @throws  \InvalidArgumentException
     * @return  self
     *
     * @since   3.1.2
     */
    public static function createObserver(\JObservableInterface $observableObject, $params = array())
    {
        $observer = new self($observableObject);
        $observer->typeAliasPattern = ArrayHelper::getValue($params, 'typeAlias');
        $observer->sendNotification = ArrayHelper::getValue($params, 'send_notification', false, 'bool');
        $observer->storeActivity    = ArrayHelper::getValue($params, 'store_activity', false, 'bool');

        return $observer;
    }

    /**
     * Pre-processor for $table->store($data)
     *
     * @param   string   $context
     * @param   int      $value
     * @param   Points   $points
     * @param   array    $options
     *
     * @throws  \RuntimeException
     *
     * @return  void
     */
    public function onAfterPointsIncrease($context, $value, Points $points, array $options = array())
    {
        // Check for allowed context.
        if (!in_array($context, $this->allowedContext, true)) {
            return;
        }

        $badgeSeeker = new BadgePointsSeeker(\JFactory::getDbo());
        $badgeSeeker->setUserPoints($points);

        $newBadge = $badgeSeeker->find();

        if ($newBadge !== null) {
            $badgeManager = new BadgeManager(\JFactory::getDbo());
            $badgeManager->setBadge($newBadge);

            $userBadge = $badgeManager->give($context, $points->getUserId(), $options);

            if ($userBadge !== null and ($this->storeActivity or $this->sendNotification)) { // Give a badge.
                $params = \JComponentHelper::getParams('com_gamification');
                $user   = \JFactory::getUser($points->getUserId());

                $communityOptions = new Registry(array(
                    'platform' => '',
                    'user_id' => $user->get('id'),
                    'context_id' => $user->get('id'),
                    'app' => 'gamification.badge'
                ));

                $activityService = $params->get('integration_activities');
                if ($this->storeActivity and $activityService) {
                    $communityOptions['platform'] = $activityService;

                    $message = \JText::sprintf('LIB_GAMIFICATION_BADGING_RECEIVE_NEW_BADGE', $user->get('name'), $newBadge->getTitle());
                    Helper::storeActivity($message, $communityOptions);
                }

                $notificationService = $params->get('integration_notifications');
                if ($this->sendNotification and $notificationService) {
                    $communityOptions['platform'] = $notificationService;

                    $message = \JText::sprintf('LIB_GAMIFICATION_BADGING_NOTIFICATION', $newBadge->getTitle());
                    Helper::sendNotification($message, $communityOptions);
                }
            }
        }
    }
}
