<? //<div class="quickscope">   ?>
<img class="quickscope_avatar" src="<?= CourseAvatar::getAvatar($course->id)->getURL(Avatar::MEDIUM) ?>">
<div class="quickscope_text">
    <h3><?= htmlReady($course->getFullname()) ?></h3>
    <p>
        <?=
        join(', ', array_map(function($obj) {
                    return $obj->user->getFullname();
                }, $course->getMembersWithStatus('dozent')))
        ?>
    </p>
    <p>
    <?= $sem->getDatesHTML() ?>
    </p>
        <? if ($collisions): ?>
            <? foreach ($collisions as $course => $count): ?>
            <p class="collisions">
            <?= sprintf(_('%s Überschneidungen mit %s'), $count, $course) ?>
            </p>
    <? endforeach; ?>
<? endif; ?>
</div>
<? //</div>   ?>
