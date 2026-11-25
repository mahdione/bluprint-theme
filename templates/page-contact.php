<?php
/**
 * Template Name: تماس با ما
 * Template Post Type: page
 *
 * The structured content (hero, support channels, social link, form copy)
 * is edited in the page editor under "محتوای صفحه تماس با ما". Messages are
 * delivered by email through a nonce-, honeypot-, timestamp- and rate-limited
 * handler defined in inc/contact-page.php.
 */
defined('ABSPATH')||exit;

get_header();

while(have_posts()):
    the_post();

    $data=bluprint_contact_get_data(get_the_ID());
    $title=trim($data['title'])!==''?$data['title']:get_the_title();
    $intro=trim($data['intro']);
    $channels=bluprint_contact_channels($data);
    $messages=bluprint_contact_error_messages();

    $status=isset($_GET['contact_status'])?sanitize_key(wp_unslash($_GET['contact_status'])):'';
    $error=isset($_GET['contact_error'])?sanitize_key(wp_unslash($_GET['contact_error'])):'';
    $notice=null;
    if($status==='success'){
        $notice=[
            'state'=>'success',
            'icon'=>'check',
            'text'=>'پیام شما با موفقیت ارسال شد. به‌زودی با شما تماس می‌گیریم.',
        ];
    }elseif($status==='error'){
        $notice=[
            'state'=>'error',
            'icon'=>'warning',
            'text'=>$messages[$error]??'لطفاً فرم را بررسی کرده و دوباره ارسال کنید.',
        ];
    }
    $invalid=in_array($error,['name','phone','message'],true)?$error:'';
?>
<main id="main-content" class="bp-contact">
    <section class="bp-contact-hero" aria-labelledby="bp-contact-title">
        <div class="bp-contact-hero__grid" aria-hidden="true"></div>
        <div class="bp-contact__container">
            <?php get_template_part('template-parts/components/breadcrumbs/breadcrumbs',null,[
                'variant'=>'hero',
                'label'=>'مسیر صفحه',
                'class'=>'bp-contact-breadcrumb',
            ]); ?>

            <div class="bp-contact-hero__layout">
                <div class="bp-contact-hero__intro-wrap">
                    <?php if(trim($data['eyebrow'])!==''): ?>
                        <p class="bp-contact-hero__eyebrow"><?php echo esc_html($data['eyebrow']); ?></p>
                    <?php endif; ?>
                    <h1 class="bp-contact-hero__title" id="bp-contact-title"><?php echo esc_html($title); ?></h1>
                    <?php if($intro!==''): ?>
                        <p class="bp-contact-hero__intro"><?php echo nl2br(esc_html($intro)); ?></p>
                    <?php endif; ?>
                </div>

                <div class="bp-contact-hero__sheet" aria-hidden="true">
                    <span>SHEET C—02</span>
                    <span>REV. <?php echo esc_html(date_i18n('Y')); ?></span>
                    <span><?php echo esc_html(get_bloginfo('name')); ?></span>
                </div>
            </div>

            <?php if(trim($data['response_note'])!==''): ?>
                <div class="bp-contact-hero__spec">
                    <span class="bp-contact-hero__spec-item">
                        <span class="bp-contact-hero__spec-key">RESPONSE</span>
                        <span class="bp-contact-hero__spec-value"><?php echo esc_html($data['response_note']); ?></span>
                    </span>
                    <span class="bp-contact-hero__rule" aria-hidden="true"></span>
                    <span class="bp-contact-hero__spec-code">C—02 / CONTACT</span>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="bp-contact-main">
        <div class="bp-contact__container">
            <div class="bp-contact-grid">
                <aside class="bp-contact-info">
                    <?php if(trim($data['info_eyebrow'])!==''): ?>
                        <p class="bp-contact-section-eyebrow"><?php echo esc_html($data['info_eyebrow']); ?></p>
                    <?php endif; ?>
                    <?php if(trim($data['info_title'])!==''): ?>
                        <h2 class="bp-contact-section-title"><?php echo esc_html($data['info_title']); ?></h2>
                    <?php endif; ?>

                    <?php if(!empty($channels)): ?>
                        <ul class="bp-contact-channels">
                            <?php foreach($channels as $index=>$channel): ?>
                                <li class="bp-contact-channel">
                                    <span class="bp-contact-channel__index" aria-hidden="true"><?php echo esc_html(sprintf('%02d',$index+1)); ?></span>
                                    <span class="bp-contact-channel__icon" aria-hidden="true"><?php echo bluprint_contact_icon($channel['icon']); ?></span>
                                    <span class="bp-contact-channel__body">
                                        <span class="bp-contact-channel__label"><?php echo esc_html($channel['label']); ?></span>
                                        <?php if($channel['url']!==''): ?>
                                            <a class="bp-contact-channel__value" href="<?php echo esc_url($channel['url']); ?>"<?php echo $channel['target']==='_blank'?' target="_blank" rel="noopener noreferrer"':''; ?> dir="ltr"><?php echo esc_html($channel['value']); ?></a>
                                        <?php else: ?>
                                            <span class="bp-contact-channel__value" dir="ltr"><?php echo esc_html($channel['value']); ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <?php if($channel['url']!==''): ?>
                                        <span class="bp-contact-channel__arrow" aria-hidden="true"><?php echo bluprint_contact_icon('arrow-left'); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if(trim($data['instagram_url'])!==''): ?>
                        <div class="bp-contact-social">
                            <span class="bp-contact-social__label">شبکه اجتماعی</span>
                            <a class="bp-contact-social__link" href="<?php echo esc_url($data['instagram_url']); ?>" target="_blank" rel="noopener noreferrer">
                                <span class="bp-contact-social__icon" aria-hidden="true"><?php echo bluprint_contact_icon('instagram'); ?></span>
                                <span class="bp-contact-social__text">
                                    <span class="bp-contact-social__handle" dir="ltr">@<?php echo esc_html(ltrim(trim($data['instagram_username'])?:'blueprint','@')); ?></span>
                                    <span class="bp-contact-social__hint">دنبال کردن صفحه اینستاگرام</span>
                                </span>
                                <span class="bp-contact-social__arrow" aria-hidden="true"><?php echo bluprint_contact_icon('arrow-left'); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if(trim($data['response_note'])!==''): ?>
                        <p class="bp-contact-info__note">
                            <span class="bp-contact-info__note-icon" aria-hidden="true"><?php echo bluprint_contact_icon('clock'); ?></span>
                            <?php echo esc_html($data['response_note']); ?>
                        </p>
                    <?php endif; ?>
                </aside>

                <div class="bp-contact-form-wrap">
                    <?php if($notice): ?>
                        <p class="bp-contact-notice is-<?php echo $notice['state']==='success'?'success':'error'; ?>" role="<?php echo $notice['state']==='success'?'status':'alert'; ?>">
                            <span class="bp-contact-notice__icon" aria-hidden="true"><?php echo bluprint_contact_icon($notice['icon']); ?></span>
                            <?php echo esc_html($notice['text']); ?>
                        </p>
                    <?php endif; ?>

                    <div class="bp-contact-form-plate">
                        <?php if(trim($data['form_title'])!==''): ?>
                            <h2 class="bp-contact-form__title"><?php echo esc_html($data['form_title']); ?></h2>
                        <?php endif; ?>
                        <?php if(trim($data['form_intro'])!==''): ?>
                            <p class="bp-contact-form__intro"><?php echo nl2br(esc_html($data['form_intro'])); ?></p>
                        <?php endif; ?>

                        <form class="bp-contact-form" method="post" action="" data-contact-form>
                            <?php wp_nonce_field('bluprint_contact_send','bluprint_contact_nonce'); ?>
                            <input type="hidden" name="bluprint_contact_time" value="<?php echo esc_attr(time()); ?>">

                            <div class="bp-contact-hp" aria-hidden="true">
                                <input type="text" id="bluprint-contact-website" name="bluprint_contact_website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="bp-field<?php echo $invalid==='name'?' is-invalid':''; ?>">
                                <label class="bp-field__label" for="bluprint-contact-name">نام و نام خانوادگی <span aria-hidden="true">*</span></label>
                                <input class="bp-field__control" type="text" id="bluprint-contact-name" name="bluprint_contact_name" autocomplete="name" maxlength="80" required>
                            </div>

                            <div class="bp-field<?php echo $invalid==='phone'?' is-invalid':''; ?>">
                                <label class="bp-field__label" for="bluprint-contact-phone">تلفن تماس <span aria-hidden="true">*</span></label>
                                <input class="bp-field__control" type="tel" id="bluprint-contact-phone" name="bluprint_contact_phone" inputmode="tel" autocomplete="tel" maxlength="20" required>
                                <span class="bp-field__hint">مثلاً ۰۹۱۲۱۲۳۴۵۶۷ یا ۰۲۱۱۲۳۴۵۶۷۸</span>
                            </div>

                            <div class="bp-field<?php echo $invalid==='message'?' is-invalid':''; ?>">
                                <label class="bp-field__label" for="bluprint-contact-message">پیام شما <span aria-hidden="true">*</span></label>
                                <textarea class="bp-field__control bp-field__control--area" id="bluprint-contact-message" name="bluprint_contact_message" rows="5" minlength="10" maxlength="2000" required></textarea>
                                <span class="bp-field__meta">
                                    <span class="bp-field__hint">حداقل ۱۰ حرف</span>
                                    <span class="bp-contact-form__counter" dir="ltr"><b data-contact-counter>0</b> / 2000</span>
                                </span>
                            </div>

                            <div class="bp-contact-form__footer">
                                <button class="bp-button bp-button--primary" type="submit" name="bluprint_contact_submit" value="1">
                                    <img class="bp-button__icon" src="<?php echo esc_url(get_theme_file_uri('assets/icons/arrow-left.svg')); ?>" alt="" aria-hidden="true" width="16" height="16" decoding="async">
                                    <span class="bp-button__label bp-button__label--desktop"><?php echo esc_html(trim($data['form_button_text'])?:'ارسال پیام'); ?></span>
                                </button>
                                <p class="bp-contact-form__privacy">اطلاعات شما فقط برای پاسخگویی استفاده می‌شود و هرگز به دیگران منتقل نمی‌شود.</p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php endwhile; ?>
<?php get_footer(); ?>