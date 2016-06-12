<?php

namespace App\Services\Toastr;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Session\SessionManager;

class Toastr
{

    /**
     * Added notifications
     *
     * @var array
     */
    protected $notifications = [];

    /**
     * Illuminate Session
     *
     * @var SessionManager
     */
    protected $session;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * Toastr constructor.
     *
     * @param SessionManager $session
     * @param Repository $config
     */
    public function __construct(SessionManager $session, Repository $config)
    {
        $this->session = $session;
        $this->config = $config;
    }

    /**
     * Render the notifications' script tag
     *
     * @return string
     * @internal param bool $flashed Whether to get the
     *
     */
    public function render()
    {
        $notifications = $this->session->get('toastr::notifications');

        if (!$notifications) $notifications = [];

        $output = '<script type="text/javascript">';
        $lastConfig = [];
        foreach ($notifications as $notification)
        {
            $config = $this->config->get('toastr.options');
            if (count($notification['options']) > 0)
            {
                // Merge user supplied options with default options
                $config = array_merge($config, $notification['options']);
            }
            // Config persists between toasts
            if ($config !== $lastConfig)
            {
                $output .= 'toastr.options = ' . json_encode($config) . ';';
                $lastConfig = $config;
            }
            // Toastr output
            $output .= 'toastr.' . $notification['type'] . "('" . str_replace("'", "\\'", str_replace(['&lt;', '&gt;'], ['<', '>'], e($notification['message']))) . "'" . (isset($notification['title']) ? ", '" . str_replace("'", "\\'", htmlentities($notification['title'])) . "'" : null) . ');';
        }
        $output .= '</script>';
        return $output;
    }

    /**
     * Add a notification.
     *
     * @param $type
     * @param $message
     * @param null $title
     * @param array $options
     * @return bool
     */
    public function add($type, $message, $title = null, array $options = [])
    {
        $allowedTypes = ['error', 'info', 'success', 'warning'];
        if (!in_array($type, $allowedTypes)) return false;
        $this->notifications[] = [
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'options' => $options
        ];
        $this->session->flash('toastr::notifications', $this->notifications);
    }

    /**
     * Shortcut for adding an info notification.
     *
     * @param $message
     * @param null $title
     * @param array $options
     */
    public function info($message, $title = null, array $options = [])
    {
        $this->add('info', $message, $title, $options);
    }

    /**
     * Shortcut for adding an error notification.
     *
     * @param $message
     * @param null $title
     * @param array $options
     */
    public function error($message, $title = null, array $options = [])
    {
        $this->add('error', $message, $title, $options);
    }

    /**
     * Shortcut for adding a warning notification.
     *
     * @param $message
     * @param null $title
     * @param array $options
     */
    public function warning($message, $title = null, $options = [])
    {
        $this->add('warning', $message, $title, $options);
    }

    /**
     * Shortcut for adding a success notification
     *
     * @param string $message The notification's message
     * @param string $title The notification's title
     */
    public function success($message, $title = null, array $options = [])
    {
        $this->add('success', $message, $title, $options);
    }

    /**
     * Clear all notifications
     */
    public function clear()
    {
        $this->notifications = [];
    }
}