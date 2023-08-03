{extends file='base.tpl'}

{block name=title}{t}Privacy statement{/t}{/block}

{assign "PRIVACY_STATEMENT_LAST_REVIEW" "2023-08-01"}
{assign "PRIVACY_STATEMENT_LAST_REVIEW_FORMAT" "Y-m-d"}

{block name=content}
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{t}Privacy statement{/t}</h3>
    </div>
    <div class="panel-body">
        <p>{t escape=no 1={$PRIVACY_STATEMENT_LAST_REVIEW|print_date_iso_format:'ll':'c':$PRIVACY_STATEMENT_LAST_REVIEW_FORMAT nofilter}}Our Privacy Policy was last updated on %1.{/t}</p>
        <p>
            {t escape=no}This Privacy Policy describes Our policies and procedures on the collection, use and disclosure of Your
            information when You use the Service.{/t}
        </p>
        <p>
            {t escape=no}We use Your Personal data to provide and improve the Service. By using the Service, You agree to the
            collection and use of information in accordance with this Privacy Policy.{/t}
        </p>

        <h4>{t}The types of personal data we use{/t}</h4>
        <p>{t}We collect and use the following information about You:{/t}</p>
        <p>
            <b>{t}Your profile information.{/t}</b>
            {t escape=no}You give us information when You register on the Platform, including Your
            username, password (cyphered), email address (cyphered), your prefered language, we also keep date and ip
            address used on registration.{/t}
        </p>
        <p>
            <b>{t}User content and behavioural information.{/t}</b>
            {t escape=no}We process the content You generate on the Platform,
            including preferences You set (such as Your "home coordinates", prefered statpic template, daily mail
            delivery preferences, OAuth association with partners), information You disclose in Your user profile such as
            Your avatar. We collect the data You post to play the game (when You create a GeoKret, post a move log, post
            comments, upload pictures). GeoKrety creation: We store the name, mission, owner and holder. Move logs: We
            store the Geokret, move type, position, country, altitude, waypoint, author, comment, application and
            version, date. Move comments: move id, author, comment, commet type, date. News comments: news id, author,
            comment. Uploaded pictures: file, author, geokret/move/user, picture caption, date. Races: organizer,
            password, title, description, race parameters (such as type, target). Races participants: GeoKret id,
            race id, move location, date. Based on Your usage of the Platform, We store username changes, yearly ranking
            statistics, awards won, messages sent between users, owner codes usage, the watched GeoKrety, news
            subscriptions.{/t}
        </p>
        <p>
            <b>{t}Tracking Technologies and Cookies.{/t}</b>
            {t escape=no
                1={GK_AUDIT_LOGS_EXCLUDE_RETENTION_DAYS}
                2={GK_AUDIT_LOGS_EXCLUDE_RETENTION_DAYS}
                3={GK_USER_AUTHENTICATION_HISTORY_RETENTION_DAYS}
                4={GK_SITE_SESSION_LIFETIME_REMEMBER / 86400}
            }
            We collect anonymized browsing usage via Our own instance of
            Matomo, You may opt-in on registration, opt-out from Your profile or configure the "Do Not Track" feature in
            Your browser. We store generic internal statistical counters of Platform parts usage (such as served pages
            count, actions types counts, rate limits and multiple internal counters). We store audit logs (for
            %1 days) and audit posts (for %2 days) about actions made on the Platform. We store User authentication history
            (for %3 days). We use only essential Cookies on the
            Platform to keep Your session alive between page loads, and allow GeoKrety Tool Kit (GKT) to work. On
            connection to the Platform, You may choose to check the "Remember me" option to stay connected for %4 days,
            this will keep the "PHPSESSID" cookie in Your browser for that period of time.{/t}
        </p>

        <h4>{t}How do we use information{/t}</h4>
        <p>{t}GeoKrety.org may use Personal Data for the following purposes:{/t}</p>
        <ul>
            <li>
                <p>
                    {t escape=no}To provide and maintain our Service, including to monitor the usage, analyze trends of our Service.{/t}
                </p>
            </li>
            <li>
                <p>
                    {t escape=no}To manage Your registration as a user of the Service. The Personal Data You provide can give You
                    access to different functionalities of the Service that are available to You as a registered user.{/t}
                </p>
            </li>
            <li>
                <p>
                    {t escape=no}To contact You by email regarding updates or informative communications related to the
                    functionalities, when necessary or reasonable for their implementation.{/t}
                </p>
            </li>
            <li>
                <p>
                    {t escape=no}To contact You by email regarding Your GeoKrety activity, watched GeoKrety activity, news
                    subscription activity, based on Your preference about "daily mails".{/t}
                </p>
            </li>
            <li>
                <p>
                    {t escape=no 1={GK_AUDIT_LOGS_EXCLUDE_RETENTION_DAYS} 2={GK_AUDIT_LOGS_EXCLUDE_RETENTION_DAYS} 3={', '|implode:GK_AUDIT_LOGS_EXCLUDE_PATH}}
                        Audit logs (for %1 days) and audit posts (for
                        %2 days) are kept to monitor data send to our servers in order
                        to detect Platform attacks or help dignose issues. This excludes posts for pages requiring user's
                        credentials (such as %3).
                    {/t}
                </p>
            </li>
            <li>
                <p>
                    {t escape=no 1={GK_USER_AUTHENTICATION_HISTORY_RETENTION_DAYS}}
                        We store User authentication history (for %1 days) in order to allow You to monitor the login
                        activity on Your profile.
                    {/t}
                </p>
            </li>
            <li>
                <p>
                    {t}Rate limit counters are used to prevent service usage abuse. Activity is kept for:{/t}<br>
                    {foreach from=GK_RATE_LIMITS key=key item=value}
                        {$key} => {'seconds'|print_interval_for_humans:$value[1]}
                        <br>
                    {/foreach}
                </p>
            </li>
        </ul>

        <h4>{t}How is collected information kept safe?{/t}</h4>
            <p>
                {t escape=no}We maintain physical, electronic, and procedural safeguards to protect the confidentiality and security
                of your personal user data and other information transmitted to us. For example, we encrypt your email
                address. If not all requests from you to the server are created over a secure connection (HTTPS).
                However, no data transmission over the Internet or other network can be guaranteed to be 100% secure.{/t}
            </p>

        <h4>{t}Apps, websites, and third-party integrations{/t}</h4>
            <p>
                {t escape=no}
                    When You choose to use third-party apps, websites, or other services that use, or are integrated
                    with GeoKrety.org, they can receive information about what You post or share. For example, when You
                    share Your "secid" with third-party, You allow them to read Your own inventory and post move, move
                    comments, pictures using Your identity. Information collected  by these third-party services is
                    subject to their own term and policy, not this one.
                {/t}
            </p>
    </div>
</div>
{/block}
