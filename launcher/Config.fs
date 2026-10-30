module Config

open System.IO
open System.Reflection

let name = "Eclipse"
let domain = "idkhowtowhistle.dpdns.org"

// the executables shipped in the setup package are still Mercury-branded
// builds, so the install paths keep that prefix while the UI says Eclipse
let clientPrefix = "Mercury"

let readEmbeddedResource (resourceName: string) =
    let assembly = Assembly.GetExecutingAssembly()
    use stream = assembly.GetManifestResourceStream resourceName

    if isNull stream then
        failwithf $"Resource '{resourceName}' not found in assembly."
    else
        use memoryStream = new MemoryStream()
        stream.CopyTo memoryStream
        memoryStream.ToArray()

let icon = readEmbeddedResource "MercuryLauncher.iconlight.png"
