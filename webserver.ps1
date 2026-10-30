# Dr. Y.S. Parmar UHF Nauni - Local Multi-Route Web Server
$port = 8000
$root = $PSScriptRoot

$listener = [System.Net.Sockets.TcpListener]::new([System.Net.IPAddress]::Any, $port)
try {
    $listener.Start()
    Write-Output "=========================================================="
    Write-Output " Dr. Y.S. Parmar UHF Nauni - Guest House Portal Active"
    Write-Output " Public Portal:    http://localhost:$port"
    Write-Output " Admin Dashboard:  http://localhost:$port/preview.html"
    Write-Output " LAN Network Link: http://10.1.1.18:$port"
    Write-Output "=========================================================="
    Write-Output "Server running. Press Ctrl+C in terminal to stop."

    while ($true) {
        $client = $listener.AcceptTcpClient()
        try {
            $stream = $client.GetStream()
            $stream.ReadTimeout = 3000
            $stream.WriteTimeout = 3000

            $buffer = New-Object byte[] 4096
            $bytesRead = $stream.Read($buffer, 0, $buffer.Length)
            if ($bytesRead -gt 0) {
                $requestStr = [System.Text.Encoding]::ASCII.GetString($buffer, 0, $bytesRead)
                $firstLine = $requestStr.Split("`r`n")[0]
                $parts = $firstLine.Split(" ")
                $url = "/"
                if ($parts.Length -gt 1) {
                    $url = $parts[1].Split("?")[0]
                }

                $filePath = Join-Path $root "index.html"
                $contentType = "text/html; charset=utf-8"

                if ($url -eq "/" -or $url -eq "/index.html" -or $url -eq "/home") {
                    $filePath = Join-Path $root "index.html"
                } elseif ($url -eq "/preview.html" -or $url -eq "/admin" -or $url -eq "/dashboard") {
                    $filePath = Join-Path $root "preview.html"
                } elseif ($url -eq "/university_logo.jpg" -or $url -eq "/logo.jpg" -or $url -eq "/images/university_logo.jpg") {
                    $filePath = Join-Path $root "university_logo.jpg"
                    $contentType = "image/jpeg"
                } elseif ($url -eq "/track") {
                    $filePath = Join-Path $root "index.html"
                } else {
                    $candidate = Join-Path $root ($url.TrimStart("/"))
                    if (Test-Path $candidate) {
                        $filePath = $candidate
                        if ($candidate.EndsWith(".jpg") -or $candidate.EndsWith(".jpeg")) { $contentType = "image/jpeg" }
                        elseif ($candidate.EndsWith(".png")) { $contentType = "image/png" }
                        elseif ($candidate.EndsWith(".css")) { $contentType = "text/css" }
                        elseif ($candidate.EndsWith(".js")) { $contentType = "application/javascript" }
                    }
                }

                if (Test-Path $filePath) {
                    $contentBytes = [System.IO.File]::ReadAllBytes($filePath)
                    $header = "HTTP/1.1 200 OK`r`nContent-Type: $contentType`r`nContent-Length: $($contentBytes.Length)`r`nAccess-Control-Allow-Origin: *`r`nConnection: close`r`n`r`n"
                    $headerBytes = [System.Text.Encoding]::UTF8.GetBytes($header)
                    $stream.Write($headerBytes, 0, $headerBytes.Length)
                    $stream.Write($contentBytes, 0, $contentBytes.Length)
                } else {
                    $notFound = [System.Text.Encoding]::UTF8.GetBytes("HTTP/1.1 404 Not Found`r`nContent-Type: text/plain`r`nConnection: close`r`n`r`nFile Not Found")
                    $stream.Write($notFound, 0, $notFound.Length)
                }
                $stream.Flush()
            }
        } catch {
            # Ignore socket drops
        } finally {
            $client.Close()
        }
    }
} catch {
    Write-Error "Server error: $($_.Exception.Message)"
} finally {
    $listener.Stop()
}
