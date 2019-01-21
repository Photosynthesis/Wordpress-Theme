module BoardStaff exposing (main)

import Admin.BoardStaff exposing (Profile, decodeProfile)
import Html exposing (Html, h1, h2, div, img, text, hr)
import Html.Attributes exposing (class, src, style)
import Json.Decode as Decode exposing (Value, decodeValue)
import Markdown


main : Program Value Model Msg
main =
    Html.programWithFlags
        { init = init
        , update = update
        , view = view
        , subscriptions = always <| Sub.none
        }


type alias Model =
    { board : List Profile
    , staff : List Profile
    }


type alias Msg =
    ()


init : Value -> ( Model, Cmd Msg )
init json =
    let
        modelDecoder =
            Decode.map2 Model
                (Decode.field "board" <| Decode.list decodeProfile)
                (Decode.field "staff" <| Decode.list decodeProfile)

        model =
            case decodeValue modelDecoder json of
                Err _ ->
                    Model [] []

                Ok m ->
                    m
    in
        ( model, Cmd.none )


update : Msg -> Model -> ( Model, Cmd Msg )
update _ m =
    ( m, Cmd.none )


view : Model -> Html msg
view m =
    div []
        [ h1 [ class "mb-4" ] [ text "FIC Staff" ]
        , div [] <| List.intersperse (hr [] []) <| List.map renderProfile m.staff
        , hr [] []
        , h1 [ class "my-4" ] [ text "Board of Directors" ]
        , div [] <| List.intersperse (hr [] []) <| List.map renderProfile m.board
        ]


renderProfile : Profile -> Html msg
renderProfile p =
    div [ style [ ( "font-size", "1rem" ) ] ]
        [ h2 [] [ Markdown.toHtml [] p.name ]
        , div [ class "clearfix" ]
            [ img [ class "alignleft", src p.image, style [ ( "max-width", "25%" ) ] ] []
            , Markdown.toHtml [] p.bio
            ]
        ]
