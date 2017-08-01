port module Ports exposing (..)

{-| Contains Every Javascript Port Used in the Application.
-}


{-| Scroll to An Element
-}
port scrollTo : String -> Cmd msg


{-| Set the Title of the Page
-}
port setPageTitle : String -> Cmd msg
