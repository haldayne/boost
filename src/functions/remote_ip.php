<?php
namespace Haldayne\Boost;

/**
 * Return the IPv4 or v6 address of the connecting client.
 *
 * The connecting client might be a proxy. If you care to know the IP of the
 * client using the proxy, you must rely upon proxy-provided headers: **which
 * can be spoofed**.
 *
 * If you want a reliable IP that may not uniquely represent the actual
 * connecting client, use this function. But if you want a less reliable,
 * though often more accurate proxy-client IP address, use the `header_ip`
 * function.
 * 
 * @return null|string The connecting client IP, up to 45 ASCII characters
 * long or null if using the CLI SAPI.
 * @see http://stackoverflow.com/a/3003233/2908724
 */
function remote_ip()
{
    return $_SERVER['REMOTE_ADDR'];
}
