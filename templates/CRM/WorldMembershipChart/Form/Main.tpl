{* HEADER *}

<div class="crm-block crm-form-block crm-Main-form-block">
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
  <div>
    <table class="form-layout">
      <tr class="crm-Main-form-block-membership_type">
        <td class="label">
          {$form.membership_type.label}
        </td>
        <td>
          {$form.membership_type.html|crmAddClass:'huge'}<br />
        </td>
      </tr>
      <tr class="crm-Main-form-block-membership_name">
        <td class="label">
          {$form.membership_name.label}
        </td>
        <td>
          {$form.membership_name.html|crmAddClass:'huge'}<br />
        </td>
      </tr>
      <tr class="crm-Main-form-block-color_min">
        <td class="label">
          {$form.color_min.label}
        </td>
        <td>
          {$form.color_min.html|crmAddClass:'huge'}<br />
          <span class="description">{ts domain="cat.babu.worldmembershipchart"}By default: #F8DAD9.{/ts}</span>
        </td>
      </tr>
      <tr class="crm-Main-form-block-color_max">
        <td class="label">
          {$form.color_max.label}
        </td>
        <td>
          {$form.color_max.html|crmAddClass:'huge'}<br />
          <span class="description">{ts domain="cat.babu.worldmembershipchart"}By default: #D9251D.{/ts}</span>
        </td>
      </tr>
      <tr class="crm-Main-form-block-division_num">
        <td class="label">
          {$form.division_num.label}
        </td>
        <td>
          {$form.division_num.html|crmAddClass:'huge'}<br />
          <span class="description">{ts domain="cat.babu.worldmembershipchart"}By default: 5.<br />First and last color not included{/ts}</span>
        </td>
      </tr>
      <tr class="crm-Main-form-block-division_point">
        <td class="label">
          {$form.division_point.label}
        </td>
        <td>
          {$form.division_point.html|crmAddClass:'huge'}<br />
          <span class="description">{ts domain="cat.babu.worldmembershipchart"}By default: 50,200,500,1000,10000. <br />Numbers separated by commas, Like: 5,10,100...(1 number for division){/ts}</span>
        </td>
      </tr>
      <tr class="crm-Main-form-block-idioma">
        <td class="label">
          {$form.idioma.label}
        </td>
        <td>
          {$form.idioma.html|crmAddClass:'huge'}<br />
        </td>
      </tr>
    </table>
  </div>
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
{if $data}
<div id="Code">
  <fieldset>
    <tr>
      <td>
        {literal}
        <style type="text/css">
          .context-menu {
            cursor: pointer;
          }

          a:hover {
            color: blue;
          }
        </style>
        <script type="text/javascript">
          function copy(element) {
            let textarea = document.getElementById(element);
            textarea.select();
            document.execCommand("copy");
          }
        </script>
        {/literal}
        <div>
          <h3>CSS</h3>
          <span class="description">{ts domain="cat.babu.worldmembershipchart"}Copy this in the 'head'{/ts}</span>
          <pre cols='70'>
                <textarea cols='89' rows='10' id="p1" readonly>{$css}</textarea>
              </pre>
          <a class="context-menu" onclick="copy('p1')">Copiar</a>
        </div>
        <br><br><br>
        <div>
          <h3>HTML</h3>
          <span class="description">{ts domain="cat.babu.worldmembershipchart"}Copy this in the 'body'{/ts}</span>
          <pre cols='70'>
                <textarea cols='89' rows='10' id="p4" readonly>{$body}</textarea>
              </pre>
          <a class="context-menu" onclick="copy('p4')">Copiar</a>
        </div>
        <br><br><br>
        <div>
          <h3>SCRIPT</h3>
          <span class="description">{ts domain="cat.babu.worldmembershipchart"}Copy this in the 'Footer'{/ts}</span>
          <pre cols='70'>
                <textarea cols='89' rows='7' id="p2" readonly>{$script}</textarea>
              </pre>
          <a class="context-menu" onclick="copy('p2')">Copiar</a>
        </div>
        <br><br><br>
        <div>
          <h3>DATA</h3>
          <span class="description">{ts domain="cat.babu.worldmembershipchart"}Copy this in the 'Footer'{/ts}</span>
          <pre cols='70'>
                <textarea cols='89' rows='10' id="p3" readonly>{$data}</textarea>
              </pre>
          <a class="context-menu" onclick="copy('p3')">Copiar</a>
        </div>
      </td>
    </tr>
  </fieldset>
</div>
{/if}
