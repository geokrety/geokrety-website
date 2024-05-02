# See http://robotframework.org/robotframework/latest/RobotFrameworkUserGuide.html#toc-entry-628

from SeleniumLibrary import SeleniumLibrary
from SeleniumLibrary.base import keyword


class Browser(SeleniumLibrary):

    @keyword
    def open_new_tab(self):
        self.driver.switch_to.new_window('tab')

    @keyword
    def go_to_url_in_new_tab(self, url):
        len_before = len(self.driver.window_handles)
        self.open_new_tab()
        len_after = len(self.driver.window_handles)
        if len_before + 1 != len_after:
            raise AssertionError("Failed to open new tab")

        self.go_to(url)
        self.location_should_be(url)

    @keyword
    def close_tab(self):
        len_before = len(self.driver.window_handles)
        self.driver.close()
        len_after = len(self.driver.window_handles)
        if len_before - 1 != len_after:
            raise AssertionError("Failed to close tab")
        self.driver.switch_to.window(self.driver.window_handles[-1])
