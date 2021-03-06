port module Directory.Ports exposing (onUrlChange, pushUrl, scrollTo, setPageTitle)

{-| Contains Every Javascript Port Used in the Application.
-}


{-| Scroll to An Element
-}
port scrollTo : String -> Cmd msg


{-| Set the Title of the Page
-}
port setPageTitle : String -> Cmd msg


{-| Set the Page URL.
-}
port pushUrl : String -> Cmd msg


{-| React to a URL change.
-}
port onUrlChange : (String -> msg) -> Sub msg
