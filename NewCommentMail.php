<?php
namespace GDO\Comments;

use GDO\Core\GDO;
use GDO\Core\GDO_DBException;
use GDO\Core\GDO_ExceptionFatal;
use GDO\Core\ModuleLoader;
use GDO\DB\Cache;
use GDO\Mail\Mail;
use GDO\User\GDO_User;

final class NewCommentMail
{
    /**
     * @throws GDO_DBException
     * @throws GDO_ExceptionFatal
     */
    public static function sendMails(GDO_Comment $comment): void
    {
        $ct = self::gdoCommentsTable($comment);
        $obj = $ct->getCommentedObjectByComment($comment);

        $users = $ct->select('comment_creator_t.*')->
            where("comment_object={$obj->getID()}")->
            joinObject('comment_id')->
            join('LEFT JOIN gdo_user AS comment_creator_t ON comment_creator=comment_creator_t.user_id')->
            fetchTable(GDO_User::table())->
            debug()->
            exec();

        $creator = $comment->getCreator();

        self::sendMail($creator, $comment, $ct, $obj);

        while ($user = $users->fetchObject())
        {
            if ($user !== $creator)
            {
                self::sendMail($user, $comment, $ct, $obj);
            }
        }
    }

    /**
     * @throws GDO_DBException
     */
    private static function gdoCommentsTable(GDO_Comment $comment): GDO_CommentTable
    {
        $modules = ModuleLoader::instance()->getEnabledModules();
        foreach ($modules as $module)
        {
            foreach ($module->getClasses() as $classname)
            {
                $table = call_user_func([$classname, 'table']);
                if ($table instanceof GDO_CommentTable)
                {
                    if ($table->getWhere("comment_id={$comment->getID()}"))
                    {
                        return $table;
                    }
                }
            }
        }
    }

    private static function sendMail(GDO_User $user, GDO_Comment $comment, GDO_CommentTable $ct, GDO $obj): void
    {
        $mail = Mail::botMail();
        $mail->setSubject(tusr($user, 'mails_new_comment'));
        $args = [
            $user->renderUserName(),
            sitename(),
            $comment->getCreator()->renderUserName(),
            $obj->renderName(),
            $comment->displayMessage(),
        ];
        $mail->setBody(tusr($user, 'mailb_new_comment', $args));
        $mail->sendToUser($user);
    }

}
