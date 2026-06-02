<?php

/**
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 */

$pager->setSurroundCount(2);
?>

<ul class="pagination">
    <?php if ($pager->hasPreviousPage()) : ?>
        <li><a href="<?= $pager->getFirst() ?>" aria-label="First">&laquo;</a></li>
        <li><a href="<?= $pager->getPrevious() ?>" aria-label="Previous">&lsaquo;</a></li>
    <?php endif; ?>

    <?php foreach ($pager->links() as $link) : ?>
        <li class="<?= $link['active'] ? 'active' : '' ?>">
            <a href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
        </li>
    <?php endforeach; ?>

    <?php if ($pager->hasNextPage()) : ?>
        <li><a href="<?= $pager->getNext() ?>" aria-label="Next">&rsaquo;</a></li>
        <li><a href="<?= $pager->getLast() ?>" aria-label="Last">&raquo;</a></li>
    <?php endif; ?>
</ul>
