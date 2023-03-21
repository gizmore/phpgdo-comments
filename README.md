# phpgdo-comments

Comments Module for GDOv7, Write, Administrate and display comments for arbritary GDO.
Optionally, comments need to be approved.

## phpgdo-comments: Usage

To enable commenting for a GDO, use the following boilerplate code in your GDO.

    use CommentedObject;
    public function gdoCommentTable() { return GDO_NewsComments::table(); }
    public function gdoCommentsEnabled() { return $this->isVisible() && $this->gdoCommentTable()->gdoEnabled(); }
    public function gdoCanComment(GDO_User $user) { return true; }

Then you need to create a method to write a comment for your GDO.

    final class WriteComment extends Comments_Write

That's it, commenting enabled, editing, approval etc. is then possible via Module_Comments admin section.

### phpgdo-comments: Dependencies

- Mail

#### phpgdo-comments: License

This module is licensed under the GDOv7 license.
