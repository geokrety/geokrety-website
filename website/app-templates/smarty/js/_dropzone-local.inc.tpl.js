function parseS3UploadError(errorMessage, xhr) {
    let response = $($.parseXML(errorMessage));
    let code = response.find("Code").text();
    if (xhr.status === 400) {
        if (code === 'EntityTooLarge') {
            return "{t}Your upload exceeds the maximum allowed size.{/t}";
        }
        if (code === 'EntityTooSmall') {
            return "{t}Your upload does not meet the minimum allowed size.{/t}";
        }
    }
    if (xhr.status === 403) {
        if (code === 'AccessDenied') {
            return "{t}Invalid according to Policy: Policy Condition failed.{/t}";
        }
    }
    console.log('This error message is not caught:', errorMessage);
    return errorMessage;
}
