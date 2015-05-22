<? //<div class="quickscope">    ?>
<img class="quickscope_avatar" src="<?= Avatar::getAvatar($user->id)->getURL(Avatar::MEDIUM) ?>">
<div class="quickscope_text">
    <h3><?= htmlReady($user->getFullname()) ?></h3>

    <? if (!empty($user->motto)) : ?>
        <p><?= htmlReady($user->motto) ?></p>
    <? endif ?>

    <? if ($user->Email) : ?>
        <p><?= _("E-Mail:") ?>
        <?= htmlReady($user->Email) ?>
        </p>
    <? endif ?>
        
</div>
<? //</div>   ?>
