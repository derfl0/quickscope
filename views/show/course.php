<img class="quickscope_avatar" src="<?= CourseAvatar::getAvatar($course->id)->getURL(Avatar::MEDIUM) ?>">
<div class="quickscope_text">
    <h3><?= htmlReady($course->getFullname()) ?></h3>
    <p>
        More info
    </p>
    <p>
        Even more info
    </p>
</div>