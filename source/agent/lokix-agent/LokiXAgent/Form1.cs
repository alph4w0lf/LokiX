using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Diagnostics;
using System.Drawing;
using System.IO;
using System.IO.Compression;
using System.Net;
using System.Runtime.CompilerServices;
using System.Text;
using System.Threading;
using System.Windows.Forms;

namespace LokiXAgent

{
    public partial class Form1 : Form
    {
        public Form1()
        {
            InitializeComponent();
        }

        private void Form1_Load(object sender, EventArgs e)
        {
            checker.Enabled = false;
            heartbeat.Enabled = false;

            // Init Platform IP
            PlatformConnector.initPlatformIp();


            // Register scan and get an API token
            bool gotToken = PlatformConnector.initScan();

            if (gotToken)
            {
                // Start the Heartbeat Timer (every 5 minutes interval)
                heartbeat.Enabled = true;

                // Initlizse Loki
                Loki.initLoki(Path.GetTempPath() + "lokix");

                // Download the Loki Zip file + Extract its contents
                Loki.getLoki();

                // Start Loki Sweep
                Loki.execute();

                // Start the Loki Execution Cheaker timer (every 1 minute interval)
                checker.Enabled = true;
            }
            
        }

        private void checker_Tick(object sender, EventArgs e)
        {
            this.Enabled = false;

            // Check that loki is still running
            if (Loki.isDone())
            {
                // No need for heartbeats
                heartbeat.Enabled = false;

                // Submit Loki Scan Results to the platform
                string loki_results = Loki.getResults();
                PlatformConnector.submitResults(loki_results);

                // Remove Loki files
                Loki.removeLoki();

                // Remove LokiXAgent and Exit application
                PlatformConnector.deleteYourself();

            } else
            {
                // Check again after 1 minute
                this.Enabled = true;
            }
        }

        private void heartbeat_Tick(object sender, EventArgs e)
        {
            this.Enabled = false;

            // Tell the platform you are still running
            PlatformConnector.sendHeartbeat();

            // Run again after 5 minutes
            this.Enabled = true;
        }
    }

    // This class contains all methods to communicate and handle tasks related to communication with the LokiX Platform
    public static class PlatformConnector
    {
        // Global Variables
        public static string PLATFORM_IP;
        public static string API_TOKEN;
        public static string HOSTNAME;
        public static string CLIENT_IP;


        // Initialize LokiX Platform IP from embedded data
        public static void initPlatformIp()
        {
            // read your own file and extract platform ip appended at the end in this format: #PLATFORM_IP#
            using (var reader = new StreamReader(System.Reflection.Assembly.GetExecutingAssembly().Location))
            {
                // Read the last 17 bytes = MAXIMUM IP SIZE plus two # marks
                try
                {
                    reader.BaseStream.Seek(-17, SeekOrigin.End);
                    string last_17 = reader.ReadToEnd();
                    // Parse the ip address
                    PLATFORM_IP = last_17.Substring(last_17.IndexOf("#") + 1);
                    PLATFORM_IP = PLATFORM_IP.Substring(0, PLATFORM_IP.Length - 1);
                } catch
                {
                    Application.Exit();
                }
            }
        }

        // Initiialize Scan and Get API Token
        public static bool initScan()
        {
            // Init hostname and ip address fields for the machine you are running on
            HOSTNAME = Dns.GetHostName();
            IPHostEntry host_entry = Dns.GetHostEntry(HOSTNAME);
            foreach (IPAddress IP in host_entry.AddressList)
            {
                if (IP.AddressFamily == System.Net.Sockets.AddressFamily.InterNetwork)
                {
                    CLIENT_IP = Convert.ToString(IP);
                }
            }

            // Send Start Scan/Registrion Request to LokiX API
            try
            {
                string response = postToApi("https://" + PLATFORM_IP + "/be/api/scan_start", "{\"hostname\":\""+HOSTNAME+"\", \"ip_address\":\""+CLIENT_IP+"\"}");
                if (jsonGetValue(response, "status") == "success")
                {
                    API_TOKEN = jsonGetValue(response, "token");
                    return true;
                }
                else
                {
                    Application.Exit();
                    return false;
                }
            } catch
            {
                Application.Exit();
                return false;
            }
        }

        // Report execution error to the platform and exit
        public static void reportErrorExit(String error_msg)
        {
            _ = postToApi("https://" + PLATFORM_IP + "/be/api/scan_fail", "{" + "\"token\":\"" + API_TOKEN + "\", \"reason\":\"" + error_msg + "\"}");
            // delete yourself and exit
            deleteYourself();
        }

        // Delete LokiX Agent
        public static void deleteYourself()
        {
            // Execute the delete command after a 5 second wait, so that LokiX agent could end execution for the delete to be successful
            try
            {
                ProcessStartInfo delStartInfo = new ProcessStartInfo();
                delStartInfo.FileName = "cmd.exe";
                delStartInfo.Arguments = "/C timeout /T 5 /NOBREAK & DEL \"" + Application.ExecutablePath + "\"";
                delStartInfo.UseShellExecute = false;
                delStartInfo.CreateNoWindow = true;

                Process delProc = new Process();
                delProc.StartInfo = delStartInfo;
                delProc.Start();

                Application.Exit();
            } catch
            {
                Application.Exit();
            }
        }

        // Submit Results to the platform
        public static void submitResults(String results)
        {
            // Count alrts/warnings/notices
            int alerts = 0;
            int warnings = 0;
            int notices = 0;
            int completed = 1;
            try
            {
                string[] stats = { "alerts", "warnings", "notices" };
                foreach (string stat_type in stats)
                {
                    string parseTemp = "";
                    int index_ptr = results.IndexOf(stat_type) - 2;
                    bool spaceFound = false;
                    while (!spaceFound)
                    {
                        parseTemp = results[index_ptr].ToString() + parseTemp;
                        index_ptr--;
                        if(results[index_ptr] == ' ')
                        {
                            spaceFound = true;
                            int numResult = Int32.Parse(parseTemp);
                            if(completed == 1)
                            {
                                alerts = numResult;
                            } else if(completed == 2)
                            {
                                warnings = numResult;
                            } else
                            {
                                notices = numResult;
                            }
                            completed++;
                        }
                    }
                }
            } catch
            {
                alerts = 999;
                warnings = 999;
                notices = 999;
            }

            // Submit results
            int fail_count = 3; // how many times to try to submit results in case the request fails
            bool isFailed;
            do
            {
                isFailed = false;
                string response = postToApi("https://" + PLATFORM_IP + "/be/api/scan_done", "{" +
                    "\"token\":\"" + API_TOKEN + "\", \"results\":\"" + SimpleJSON.EscapeString(results) + "\"," +
                    "\"alerts\":\"" + alerts.ToString() + "\", \"warnings\":\"" + warnings.ToString() + "\", \"notices\":\"" + notices.ToString()
                   + "\"}");

                if (response.IndexOf("failed") >= 0)
                {
                    isFailed = true;
                    fail_count--;
                }
            } while (isFailed && fail_count > 0);

            // Report error if not done successfully
            if (isFailed)
            {
                PlatformConnector.reportErrorExit("Failed to submit results to LokiX platform");
            }
        }

        // Send a heartbeat to the platform
        public static void sendHeartbeat()
        {
            _ = postToApi("https://" + PLATFORM_IP + "/be/api/heartbeat", "{\"token\":\"" + API_TOKEN + "\"}");
        }

        // Web Communication Function
        public static string postToApi(string api_url, string jsonObject)
        {
            try
            {
                ServicePointManager.ServerCertificateValidationCallback = delegate { return true; }; // Disable certificate validation
                ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls12;
                Uri uri = new Uri(api_url);
                WebRequest web_request = WebRequest.Create(uri);
                web_request.Method = "POST";
                web_request.ContentType = "application/json";
                using (StreamWriter post_data = new StreamWriter(web_request.GetRequestStream()))
                {
                    post_data.Write(jsonObject);
                }
                WebResponse web_response = web_request.GetResponse();
                string response_string;
                // Read response and close stream and dispose it
                using (StreamReader response_reader = new StreamReader(web_response.GetResponseStream()))
                {
                    response_string = response_reader.ReadToEnd();
                }
                return response_string;
            } catch
            {
                return "failed";
            }
            
        }

        // Retrieve the value of a name from a json object string (Trying to avoid serialization use and third party libs)
        // Disclaimer: This function is not a reliable json parser, but suitable for the API used for the LokiX agent
        public static string jsonGetValue(string jsonObject, string key_name)
        {
            int start_index = jsonObject.IndexOf("\"" + key_name + "\"");
            string closer_to_value = jsonObject.Substring(start_index + key_name.Length + 2);
            string key_value_distorted = closer_to_value.Substring(closer_to_value.IndexOf("\"")+1);
            string clean_value = key_value_distorted.Substring(0,key_value_distorted.IndexOf("\""));
            return clean_value;
        }

    }

    
    // This class contains all the logic needed to execute and maintain a Loki Scan
    public static class Loki
    {
        // Global Variables
        public static string LOKI_PATH;

        // Initalize loki
        public static void initLoki(string PATH)
        {
            LOKI_PATH = PATH;
            // Create Lokix folder
            try
            {
                if (Directory.Exists(LOKI_PATH))
                {
                    // Remove old folder
                    try
                    {
                        Directory.Delete(LOKI_PATH, true);
                    } catch
                    {
                        PlatformConnector.reportErrorExit("Could not remove previous lokix folder in temp");
                    }
                }
                // Create a new Lokix folder
                Directory.CreateDirectory(LOKI_PATH);
            } catch
            {
                PlatformConnector.reportErrorExit("Could not create a folder for lokix in temp");
            }
        }

        // Download Loki Zip file from the platform and extract its contents
        public static void getLoki()
        {
            // Download Loki
            try
            {
                WebClient loki_downloader = new WebClient();
                loki_downloader.DownloadFile("https://" + PlatformConnector.PLATFORM_IP + "/be/api/get/loki", LOKI_PATH+"\\loki.tar.gz");
            } catch
            {
                PlatformConnector.reportErrorExit("Failed to download Loki compressed file");
            }

            // Extract loki.gz
            try
            {
                Tar.ExtractTarGz(LOKI_PATH + "\\loki.tar.gz", LOKI_PATH);
            } catch
            {
                PlatformConnector.reportErrorExit("Failed to extract Loki compressed file");
            }
        }

        // Execute Loki Silently
        public static void execute()
        {
            try
            {
                ProcessStartInfo lokiStartInfo = new ProcessStartInfo();
                lokiStartInfo.FileName = LOKI_PATH + "\\loki\\loki.exe";
                lokiStartInfo.Arguments = "--noindicator --dontwait -l " + LOKI_PATH + "\\loki\\results.log";
                lokiStartInfo.UseShellExecute = false;
                lokiStartInfo.CreateNoWindow = true;

                Process lokiProc = new Process();
                lokiProc.StartInfo = lokiStartInfo;
                lokiProc.EnableRaisingEvents = true;
                lokiProc.Start();

            } catch
            {
                PlatformConnector.reportErrorExit("Failed to start loki");
            }
        }

        // Check if Loki has finished scanning
        public static bool isDone()
        {
            Process[] lokis = Process.GetProcessesByName("loki");
            if(lokis.Length > 0)
            {
                return false;
            }
            return true;
        }

        // Retreive Scan Results
        public static string getResults()
        {
            // read loki-results.log file
            try
            {
                string[] loki_logs = Directory.GetFiles(LOKI_PATH+"\\loki", "*.log");
                foreach (string log_file in loki_logs)
                {
                    if(log_file.IndexOf("loki-upgrade") >= 0)
                    {
                        continue;
                    }
                    return File.ReadAllText(log_file);
                }
                return "";

            } catch
            {
                PlatformConnector.reportErrorExit("Failed to read loki scan results file");
                return ""; // just to evade IDE warnings
            }
        }

        // Remove all Loki Files
        public static void removeLoki()
        {
            try
            {
                Directory.Delete(LOKI_PATH, true);
            } catch
            {
                // Do nothing .. it doesn't matter at this stage
                return;
            }
        }

    }


    // Tar Class is borrowed from ForeverZer0 with slight modifications
    // https://gist.github.com/ForeverZer0/a2cd292bd2f3b5e114956c00bb6e872b
    public class Tar
    {
        public static void ExtractTarGz(string filename, string outputDir)
        {
            using (var stream = File.OpenRead(filename))
                ExtractTarGz(stream, outputDir);
        }

        public static void ExtractTarGz(Stream stream, string outputDir)
        {
            // A GZipStream is not seekable, so copy it first to a MemoryStream
            using (var gzip = new GZipStream(stream, CompressionMode.Decompress))
            {
                const int chunk = 4096;
                using (var memStr = new MemoryStream())
                {
                    int read;
                    var buffer = new byte[chunk];
                    do
                    {
                        read = gzip.Read(buffer, 0, chunk);
                        memStr.Write(buffer, 0, read);
                    } while (read == chunk);

                    memStr.Seek(0, SeekOrigin.Begin);
                    ExtractTar(memStr, outputDir);
                }
            }
        }

        public static void ExtractTar(Stream stream, string outputDir)
        {
            var buffer = new byte[100];
            while (true)
            {
                stream.Read(buffer, 0, 100);
                var name = Encoding.ASCII.GetString(buffer).Trim('\0');
                if (name == null || name == "" || name == " ")
                {
                    break;
                }
      
                stream.Seek(24, SeekOrigin.Current);
                stream.Read(buffer, 0, 12);
                var size = Convert.ToInt64(Encoding.UTF8.GetString(buffer, 0, 12).Trim('\0').Trim(), 8);

                stream.Seek(376L, SeekOrigin.Current);

                var output = Path.Combine(outputDir, name);
                if (!Directory.Exists(Path.GetDirectoryName(output)))
                    Directory.CreateDirectory(Path.GetDirectoryName(output));
                if ((!name.Equals("./", StringComparison.InvariantCulture)) && !name.EndsWith("/"))
                {
                    using (var str = File.Open(output, FileMode.OpenOrCreate, FileAccess.Write))
                    {
                        var buf = new byte[size];
                        stream.Read(buf, 0, buf.Length);
                        str.Write(buf, 0, buf.Length);
                    }
                }

                var pos = stream.Position;

                var offset = 512 - (pos % 512);
                if (offset == 512)
                    offset = 0;

                stream.Seek(offset, SeekOrigin.Current);
            }
        }
    }

    // This class is borrowed from the Mono proejct
    // https://github.com/mono/mono/blob/master/mcs/class/System.Json/System.Json/JsonValue.cs
    public class SimpleJSON
    {

        private static bool NeedEscape(string src, int i)
        {
            char c = src[i];
            return c < 32 || c == '"' || c == '\\'
                // Broken lead surrogate
                || (c >= '\uD800' && c <= '\uDBFF' &&
                    (i == src.Length - 1 || src[i + 1] < '\uDC00' || src[i + 1] > '\uDFFF'))
                // Broken tail surrogate
                || (c >= '\uDC00' && c <= '\uDFFF' &&
                    (i == 0 || src[i - 1] < '\uD800' || src[i - 1] > '\uDBFF'))
                // To produce valid JavaScript
                || c == '\u2028' || c == '\u2029'
                // Escape "</" for <script> tags
                || (c == '/' && i > 0 && src[i - 1] == '<');
        }



        public static string EscapeString(string src)
        {
            System.Text.StringBuilder sb = new System.Text.StringBuilder();

            int start = 0;
            for (int i = 0; i < src.Length; i++)
                if (NeedEscape(src, i))
                {
                    sb.Append(src, start, i - start);
                    switch (src[i])
                    {
                        case '\b': sb.Append("\\b"); break;
                        case '\f': sb.Append("\\f"); break;
                        case '\n': sb.Append("\\n"); break;
                        case '\r': sb.Append("\\r"); break;
                        case '\t': sb.Append("\\t"); break;
                        case '\"': sb.Append("\\\""); break;
                        case '\\': sb.Append("\\\\"); break;
                        case '/': sb.Append("\\/"); break;
                        default:
                            sb.Append("\\u");
                            sb.Append(((int)src[i]).ToString("x04"));
                            break;
                    }
                    start = i + 1;
                }
            sb.Append(src, start, src.Length - start);
            return sb.ToString();
        }
    }
}
