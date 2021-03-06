<?php

namespace Bluora\Slack;

use InvalidArgumentException;

class Message
{
    /**
     * Reference to the Slack client responsible for sending
     * the message.
     *
     * @var \Bluora\Slack\Client
     */
    protected $client;

    /**
     * The text to send with the message.
     *
     * @var string
     */
    protected $text;

    /**
     * The channel the message should be sent to.
     *
     * @var string
     */
    protected $channel;

    /**
     * The username the message should be sent as.
     *
     * @var string
     */
    protected $username;

    /**
     * Post as the user instead of the bot.
     *
     * @var string
     */
    protected $as_user = false;

    /**
     * The URL to the icon to use.
     *
     * @var string
     */
    protected $icon;

    /**
     * The type of icon we are using.
     *
     * @var enum
     */
    protected $iconType;

    /**
     * Whether the message text should be interpreted in Slack's
     * Markdown-like language.
     *
     * @var bool
     */
    protected $allow_markdown = true;

    /**
     * The attachment fields which should be formatted with
     * Slack's Markdown-like language.
     *
     * @var array
     */
    protected $markdown_in_attachments = [];

    /**
     * An array of attachments to send.
     *
     * @var array
     */
    protected $attachments = [];

    /**
     * @var string
     */
    const ICON_TYPE_URL = 'icon_url';

    /**
     * @var string
     */
    const ICON_TYPE_EMOJI = 'icon_emoji';

    /**
     * Instantiate a new Message.
     *
     * @param \Bluora\Slack\Client $client
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get the message text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set the message text.
     *
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Change the channel the post will be made to.
     *
     * @param string $channel
     * @return $this
     */
    public function to($channel)
    {
        return $this->setChannel($channel);
    }

    /**
     * Get the channel we will post to.
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set the channel we will post to.
     *
     * @param string $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Change the name of the user the post will be made as.
     *
     * @param string $username
     * @return $this
     */
    public function from($username = false)
    {
        if ($username === false) {
            return $this->getUsername();
        }

        return $this->setUsername($username);
    }

    /**
     * Get the username we will post as.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the username we will post as.
     *
     * @param string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set or get the icon (either URL or emoji) we will post as.
     *
     * @return mixed
     */
    public function icon($icon = false)
    {
        if ($icon === false) {
            return $this->getIcon();
        }

        return $this->setIcon($icon);
    }

    /**
     * Get the icon (either URL or emoji) we will post as.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set the icon (either URL or emoji) we will post as.
     *
     * @param string $icon
     * @return $this
     */
    public function setIcon($icon)
    {
        if ($icon == null) {
            $this->icon = $this->iconType = null;

            return;
        }

        $this->iconType = self::ICON_TYPE_URL;

        if (mb_substr($icon, 0, 1) == ':' && mb_substr($icon, mb_strlen($icon) - 1, 1) == ':') {
            $this->iconType = self::ICON_TYPE_EMOJI;
        }

        $this->icon = $icon;

        return $this;
    }

    /**
     * Get the icon type being used, if an icon is set.
     *
     * @return string
     */
    public function getIconType()
    {
        return $this->iconType;
    }

    /**
     * Get whether message text should be formatted with
     * Slack's Markdown-like language.
     *
     * @return bool
     */
    public function isAllowMarkdown()
    {
        return $this->allow_markdown;
    }

    /**
     * Set whether message text should be formatted with
     * Slack's Markdown-like language.
     *
     * @param bool $value
     * @return void
     */
    public function setAllowMarkdown($value)
    {
        $this->allow_markdown = (bool) $value;

        return $this;
    }

    /**
     * Enable Markdown formatting for the message.
     *
     * @return void
     */
    public function enableMarkdown()
    {
        $this->setAllowMarkdown(true);

        return $this;
    }

    /**
     * Disable Markdown formatting for the message.
     *
     * @return void
     */
    public function disableMarkdown()
    {
        $this->setAllowMarkdown(false);

        return $this;
    }

    /**
     * Get the attachment fields which should be formatted
     * in Slack's Markdown-like language.
     *
     * @return array
     */
    public function getMarkdownInAttachments()
    {
        return $this->markdown_in_attachments;
    }

    /**
     * Set the attachment fields which should be formatted
     * in Slack's Markdown-like language.
     *
     * @param array $fields
     * @return void
     */
    public function setMarkdownInAttachments(array $fields)
    {
        $this->markdown_in_attachments = $fields;

        return $this;
    }

    /**
     * Chainable method for setting the icon.
     *
     * @param string $icon
     * @return $this
     */
    public function withIcon($icon)
    {
        $this->setIcon($icon);

        return $this;
    }

    /**
     * Add an attachment to the message.
     *
     * @param mixed $attachment
     * @return $this
     */
    public function attach($attachment)
    {
        if ($attachment instanceof Attachment) {
            $this->attachments[] = $attachment;

            return $this;
        } elseif (is_array($attachment)) {
            $attachmentObject = new Attachment($attachment);

            if (! isset($attachment['mrkdwn_in'])) {
                $attachmentObject->setMarkdownFields($this->getMarkdownInAttachments());
            }

            $this->attachments[] = $attachmentObject;

            return $this;
        }

        throw new InvalidArgumentException('Attachment must be an instance of Bluora\\Slack\\Attachment or a keyed array');
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set the attachments for the message.
     *
     * @param array $attachments
     * @return $this
     */
    public function setAttachments(array $attachments)
    {
        $this->clearAttachments();

        foreach ($attachments as $attachment) {
            $this->attach($attachment);
        }

        return $this;
    }

    /**
     * Remove all attachments for the message.
     *
     * @return $this
     */
    public function clearAttachments()
    {
        $this->attachments = [];

        return $this;
    }

    /**
     * Send the message.
     *
     * @param string $text The text to send
     * @return void
     */
    public function send($text = null)
    {
        if ($text) {
            $this->setText($text);
        }

        $this->client->sendMessage($this);

        return $this;
    }
}
