{extends file='base.tpl'}

{block name=content}

<div class="alert alert-info" role="alert">
    <b>This page is only intended for developer use.</b> It is enabled only if no smtp server has been configured.
</div>

<h1>Developer Mailbox</h1>
{if $f3->get('SESSION.LOCAL_MAIL')}
<div class="table-responsive">
    <table class="table table-striped" id="mailsTable">
        <thead>
            <tr>
                <th>{t}ID{/t}</th>
                <th>{t}Subject{/t}</th>
                <th>{t}From{/t}</th>
                <th>{t}To{/t}</th>
                <th class="text-right">
                    {t}Action{/t}
                    <a href="{'devel_mail_delete_all'|alias}" id="deleteAllMailsButton" class="btn btn-danger btn-xs" title="{t}Delete all mails{/t}">
                        {fa icon="trash"}
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$f3->get('SESSION.LOCAL_MAIL') item=item key=key}
            <tr {if !$item.read}class="bold"{/if}>
                <td class="mail_id">{$key}</td>
                <td>
                    <a href="{'devel_mail'|alias:sprintf('@mailid=%d', $key)}" class="displayMailLink">
                        {$item.smtp.subject}
                    </a>
                </td>
                <td>{$item.smtp.from}</td>
                <td>"{$item.smtp.to.0.1}" &lt;{$item.smtp.to.0.0}&gt;</td>
                <td class="text-right">
                    <a href="{'devel_mail_delete'|alias:sprintf('@mailid=%d', $key)}" class="btn btn-danger btn-xs deleteMailLink" title="{t}Delete mail{/t}">
                        {fa icon="trash"}
                    </a>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</div>
{else}
There is no mail.
{/if}

{/block}
