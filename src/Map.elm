module Map exposing (Coords, Map, Marker, render)

{-| This module is responsible for specifying & rendering Google Maps.

The webcomponents it creates are provided by the
`coffeekraken-s-google-map-component` npm packages.

-}

import Html exposing (Html)
import Html.Attributes exposing (attribute)
import Json.Encode as Encode exposing (Value)


type alias Map =
    { center : Maybe Coords
    , zoom : Int
    , markers : List Marker
    }


type alias Coords =
    { latitude : Float
    , longitude : Float
    }


encodeCoords : Coords -> Value
encodeCoords { latitude, longitude } =
    Encode.object [ ( "lat", Encode.float latitude ), ( "lng", Encode.float longitude ) ]


type alias Marker =
    { position : Coords
    , title : String
    }


render : Map -> Html msg
render m =
    Html.node "s-google-map"
        (List.filterMap identity
            [ Just <| attribute "api-key" "AIzaSyAGISn5aMHQ1cwSVUAUHytb40g27ul9qQE"
            , Maybe.map (attribute "center" << Encode.encode 0 << encodeCoords) m.center
            , Just <| attribute "zoom" <| toString m.zoom
            ]
        )
        (List.map renderMarker m.markers)


renderMarker : Marker -> Html msg
renderMarker m =
    Html.node "s-google-map-marker"
        [ attribute "position" <| Encode.encode 0 <| encodeCoords m.position
        , attribute "title" m.title
        ]
        []
