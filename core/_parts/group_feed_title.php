<div class="item-list-tabs no-ajax" id="subnav">
    <ul class="bprf_rss_feed_title">
        <li class="feed">
            <?php
            /** var $rss @class BPRF_Feed */
            if(!empty($rss->link)){ echo '<a href="' . $rss->link . '" target="_blank">'; }
                echo $rss->title;
            if(!empty($rss->link)) { echo '</a>'; }
            ?>
        </li>
    </ul>
</div>