<?php
namespace Haldayne\Boost;

/**
 * Return the client IP addresses provided by proxy-supplied headers,
 * including the proxy IP address itself.
 * 
 * Do not rely on these values, because they come from the headers which are
 * user-supplied and can be spoofed. (Unless you know certain headers are
 * reliable because you own the proxy infrastructure.)
 *
 * By default, we validate that the values in the headers are IP addresses.
 * You can disable this. By default, we check well-known headers, but you can
 * change which headers to check.
 *
 * To determine if the client originated from a known proxy, bearing in mind
 * that this test is tainted because it relies upon headers, you can use:
 *
 * ```
 * if (header_ip() === [ remote_ip() ]) {
 *     // client NOT using a known proxy
 * } else {
 *     // client IS using a known proxy
 * }
 * ```
 *
 * @param bool $validate Check that header-supplied values are IP addresses.
 * @param array $headers The headers to check for originating IP addresses.
 * @return array All IP addresses reported by proxy headers, with the last-
 * most element being the proxy IP address itself.
 * @see http://blog.ircmaxell.com/2012/11/anatomy-of-attack-how-i-hacked.html
 */
function header_ip($validate = true, $headers = [ ])
{
}
