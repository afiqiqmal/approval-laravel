<?php


namespace Afiqiqmal\Approval\Models;


class ApprovalModelContent
{
    protected $title;
    protected $body;
    protected $type;
    protected $who;

    /**
     * @param mixed $title
     * @return ApprovalModelContent
     */
    public function setTitle($title): ApprovalModelContent
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param mixed $body
     * @return ApprovalModelContent
     */
    public function setDescription($body): ApprovalModelContent
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param mixed $type
     * @return ApprovalModelContent
     */
    public function setType($type): ApprovalModelContent
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param mixed $who
     * @return ApprovalModelContent
     */
    public function setWho($who): ApprovalModelContent
    {
        $this->who = $who;
        return $this;
    }

    public function getContent()
    {
        return [
            'Title' => $this->title,
            'Description' => $this->body,
            'Type' => $this->type,
            'Requested By' => $this->who,
        ];
    }
}
