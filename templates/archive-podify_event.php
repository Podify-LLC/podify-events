<?php
/**
 * Template: Podify Event Archive
 */

get_header(); ?>

<div class="podify-events-archive container">

    <header class="archive-header">
        <h1 class="archive-title"><?php echo esc_html__( 'Events', 'podify-events' ); ?></h1>
    </header>

    <div class="podify-events-wrapper podify-events-layout-grid">
        <div class="podify-events-grid" role="list" style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 30px;">

        <?php if ( have_posts() ) : ?>

            <?php while ( have_posts() ) : the_post(); ?>
                <?php
                    $date_start = get_post_meta( get_the_ID(), '_podify_event_date_start', true );
                    $date_end   = get_post_meta( get_the_ID(), '_podify_event_date_end', true );
                    $date       = $date_start ? $date_start : get_post_meta( get_the_ID(), '_podify_event_date', true );
                    $time       = get_post_meta( get_the_ID(), '_podify_event_time', true );
                    $address    = get_post_meta( get_the_ID(), '_podify_event_address', true );
                    $badge_html = '';
                    if ( $date ) {
                        $now_ts   = current_time( 'timestamp' );
                        $start_ts = strtotime( $date );
                        $end_ts   = $date_end ? strtotime( $date_end ) : $start_ts;
                        if ( $start_ts ) {
                            $today      = gmdate( 'Y-m-d', $now_ts );
                            $start_day  = gmdate( 'Y-m-d', $start_ts );
                            $end_day    = gmdate( 'Y-m-d', $end_ts );
                            if ( $end_day < $today ) { $badge_html = '<div class="podify-badge" aria-hidden="true">' . esc_html__( 'Ended', 'podify-events' ) . '</div>'; }
                            elseif ( $start_day > $today ) { $badge_html = '<div class="podify-badge" aria-hidden="true">' . esc_html__( 'Upcoming', 'podify-events' ) . '</div>'; }
                            else { $badge_html = '<div class="podify-badge" aria-hidden="true">' . esc_html__( 'Ongoing', 'podify-events' ) . '</div>'; }
                        }
                    }
                ?>
                <article class="podify-event-card" role="listitem" aria-labelledby="event-title-<?php the_ID(); ?>">
                    <figure class="event-image">
                        <a class="event-image__link" href="<?php the_permalink(); ?>">
                            <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large' ); } ?>
                        </a>
                        <?php echo $badge_html; ?>
                    </figure>
                    <div class="event-content">
                        <h3 id="event-title-<?php the_ID(); ?>" class="event-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <div class="podify-event-meta">
                            <?php
                                if ( $date ) {
                                    $human = '';
                                    if ( ! empty( $date_end ) && $date_end !== $date ) { $human = date_i18n( 'F j', strtotime( $date ) ) . '–' . date_i18n( 'j Y', strtotime( $date_end ) ); }
                                    else { $human = date_i18n( 'F j Y', strtotime( $date ) ); }
                                    echo '<div class="meta-item"><span class="dashicons dashicons-calendar" aria-hidden="true"></span><span class="meta-text">' . esc_html( $human ) . '</span></div>';
                                } else {
                                    echo '<div class="meta-item"><span class="dashicons dashicons-calendar" aria-hidden="true"></span><span class="meta-text">' . esc_html__( 'TBA', 'podify-events' ) . '</span></div>';
                                }
                                if ( $time ) { echo '<div class="meta-item"><span class="dashicons dashicons-clock" aria-hidden="true"></span><span class="meta-text">' . esc_html( $time ) . '</span></div>'; }
                                if ( $address ) { echo '<div class="meta-item"><span class="dashicons dashicons-location" aria-hidden="true"></span><span class="meta-text">' . esc_html( $address ) . '</span></div>'; }
                            ?>
                        </div>
                        <div class="event-excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 20 ) ); ?></div>
                        <div class="event-actions"><a class="podify-read-more" href="<?php the_permalink(); ?>"><?php echo esc_html__( 'Learn more', 'podify-events' ); ?></a></div>
                    </div>
                </article>
            <?php endwhile; ?>

            <div class="pagination">
                <?php the_posts_pagination(); ?>
            </div>

        <?php else : ?>

            <p><?php echo esc_html__( 'No events found.', 'podify-events' ); ?></p>

        <?php endif; ?>

        </div>
    </div>

</div>

<?php get_footer(); ?>
