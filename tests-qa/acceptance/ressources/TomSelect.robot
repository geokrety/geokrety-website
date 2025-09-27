*** Settings ***
Library         libraries/Browser.py  timeout=10  implicit_wait=0

*** Keywords ***

Clear TomSelect
    [Arguments]    ${element}
    Execute Javascript
    ...    var tomSelectElement = document.evaluate('${element}', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    ...    if (tomSelectElement && tomSelectElement.tomselect) {
    ...        tomSelectElement.tomselect.clear();
    ...    } else {
    ...        console.error('TomSelect instance not found for element');
    ...    }

Debug TomSelect
    [Arguments]    ${element}
    ${debug_info}=    Execute Javascript
    ...    var el = document.evaluate('${element}', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    ...    if (!el) return 'Element not found';
    ...    if (!el.tomselect) return 'TomSelect not initialized';
    ...    var ts = el.tomselect;
    ...    console.log('Current items:', ts.items);
    ...    console.log('Available options:', Object.keys(ts.options));
    ...    console.log('Options data:', ts.options);
    ...    console.log('Is loading:', ts.loading);
    ...    return JSON.stringify({
    ...        items: ts.items,
    ...        optionKeys: Object.keys(ts.options),
    ...        loading: ts.loading,
    ...        locked: ts.isLocked
    ...    });
    Log    ${debug_info}

Wait For TomSelect Loading Complete
    [Arguments]    ${element}    ${timeout}=10s
    Wait Until Keyword Succeeds    ${timeout}    0.2s    TomSelect Loading Should Be Complete    ${element}

TomSelect Loading Should Be Complete
    [Arguments]    ${element}
    ${is_loading}=    Execute Javascript
    ...    var el = document.evaluate('${element}', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    ...    if (!el || !el.tomselect) return true;
    ...    return el.tomselect.loading === 0;
    Should Be True    ${is_loading}    TomSelect is still loading

Add TomSelect Value When Ready
    [Arguments]    ${element}    ${value}
    # Wait for loading to complete
    Wait For TomSelect Loading Complete     ${element}

    # Check if the value exists in options
    ${option_exists}=    Execute Javascript
    ...    var el = document.evaluate('${element}', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    ...    if (!el || !el.tomselect) return false;
    ...    return '${value}' in el.tomselect.options;

    Run Keyword If    not ${option_exists}    Log    Option '${value}' not found in TomSelect options    WARN

    # Add the item if option exists
    Run Keyword If    ${option_exists}    Execute Javascript
    ...    var el = document.evaluate('${element}', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    ...    if (el && el.tomselect) {
    ...        console.log('Adding item: ${value}');
    ...        el.tomselect.addItem('${value}', true);
    ...        el.tomselect.refreshItems();
    ...    }

Wait Until TomSelect Has Item Count
    [Documentation]    Wait until TomSelect selection count equals ${expected}
    [Arguments]    ${input_base}    ${expected}
    Wait Until Keyword Succeeds    15x    300ms    TomSelect Item Count Should Be    ${input_base}    ${expected}

TomSelect Item Count Should Be
    [Documentation]    Assert TomSelect selection count equals ${expected}
    [Arguments]    ${input_base}    ${expected}
    ${count}=    Get TomSelect Selected Count    ${input_base}
    Should Be Equal As Integers    ${count}    ${expected}

Get TomSelect Selected Count
    [Documentation]    Return number of selected items in TomSelect using the API items property
    [Arguments]    ${input_base}
    ${count}=    Execute Javascript
    ...    var el = document.evaluate('${input_base}', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    ...    if (!el || !el.tomselect) {
    ...        console.log('TomSelect instance not found');
    ...        return null;
    ...    }
    ...    var items = el.tomselect.items || [];
    ...    console.log('TomSelect items:', items, 'Count:', items.length);
    ...    return items.length;
    Return From Keyword    ${count}

Remove TomSelect Item
    [Documentation]    Remove item from TomSelect using the API
    [Arguments]    ${input_base}    ${value}
    Execute Javascript
    ...    var el = document.evaluate('${input_base}', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    ...    if (el && el.tomselect) {
    ...        console.log('Removing item: ${value}');
    ...        el.tomselect.removeItem('${value}');
    ...        el.tomselect.refreshItems();
    ...    } else {
    ...        console.error('TomSelect instance not found for removal');
    ...    }

Focus TomSelect Input
    [Documentation]    Focus the TomSelect control
    [Arguments]    ${input_base}
    Execute Javascript
    ...    var el = document.evaluate('${input_base}', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    ...    if (el && el.tomselect) {
    ...        el.tomselect.focus();
    ...    }

Get TomSelect Items
    [Documentation]    Return array of selected item values from TomSelect
    [Arguments]    ${input_base}
    ${items}=    Execute Javascript
    ...    var el = document.evaluate('${input_base}', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    ...    if (!el || !el.tomselect) return [];
    ...    return el.tomselect.items || [];
    Return From Keyword    ${items}

Get TomSelect Options
    [Documentation]    Return available options from TomSelect
    [Arguments]    ${input_base}
    ${options}=    Execute Javascript
    ...    var el = document.evaluate('${input_base}', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    ...    if (!el || !el.tomselect) return {};
    ...    return el.tomselect.options || {};
    Return From Keyword    ${options}

TomSelect Should Contain Item
    [Documentation]    Verify TomSelect contains the specified item
    [Arguments]    ${input_base}    ${value}
    ${items}=    Get TomSelect Items    ${input_base}
    Should Contain    ${items}    ${value}

TomSelect Should Not Contain Item
    [Documentation]    Verify TomSelect does not contain the specified item
    [Arguments]    ${input_base}    ${value}
    ${items}=    Get TomSelect Items    ${input_base}
    Should Not Contain    ${items}    ${value}
