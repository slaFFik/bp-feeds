<?php
/** @var $rss BPRF_Feed */
if( isset($rss->title) && !empty($rss->title) ) : ?>
    <div class="item-list-tabs no-ajax" id="subnav">
        <ul class="bprf_rss_feed_title">
            <li class="feed">
                <?php
                if(isset($rss->link) && !empty($rss->link)) echo '<a href="' . $rss->link . '" target="_blank">';
                    echo $rss->title;
                if(isset($rss->link) && !empty($rss->link)) echo '</a>';
                ?>
            </li>
        </ul>
    </div>
<?php endif; ?>